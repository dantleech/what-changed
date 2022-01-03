<?php

namespace DTL\WhatChanged\Adapter\Github;

use DTL\WhatChanged\Exception\WhatChangedRuntimeException;
use DTL\WhatChanged\Model\ChangeBuilder;
use DTL\WhatChanged\Model\Changelog;
use DTL\WhatChanged\Model\PackageHistory;
use Traversable;

class GithubChangelog implements Changelog
{
    /**
     * @var PackageHistory
     */
    private $history;

    /**
     * @var GithubUrlParser
     */
    private $parser;

    /**
     * @var GithubClient
     */
    private $client;

    /**
     * @var int|null
     */
    private $maxCommits;

    public function __construct(PackageHistory $history, GithubClient $client, ?int $maxCommits = null)
    {
        $this->history = $history;
        $this->client = $client;
        $this->parser = new GithubUrlParser();
        $this->maxCommits = $maxCommits;
    }

    public function getIterator(): Traversable
    {
        [$org, $repo] = $this->parser->parse($this->history->url());

        $url = sprintf(
            'https://api.github.com/repos/%s/%s/compare/%s...%s',
            $org,
            $repo,
            $this->history->first(),
            $this->history->last()
        );

        $response = $this->client->request($url);

        if (!isset($response['commits'])) {
            throw new WhatChangedRuntimeException(sprintf(
                'Unexpected response from Github: "%s"',
                json_encode($response, JSON_PRETTY_PRINT)
            ));
        }

        $count = 0;
        foreach ($response['commits'] as $commit) {
            if ($this->maxCommits && $count++ >=  $this->maxCommits) {
                return;
            }
            yield ChangeBuilder::create()
                ->rawDate($commit['commit']['author']['date'])
                ->message($commit['commit']['message'])
                ->author($commit['author']['login'] ?? $commit['committer']['login'] ?? '<???>')
                ->parents($commit['parents'])
                ->sha($commit['sha'])
                ->build();
        }
    }
}
