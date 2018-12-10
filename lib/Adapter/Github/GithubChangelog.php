<?php

namespace DTL\WhatChanged\Adapter\Github;

use DTL\WhatChanged\Model\Change;
use DTL\WhatChanged\Model\Changelog;
use DTL\WhatChanged\Model\CommitFilter;
use DTL\WhatChanged\Model\PackageHistory;
use RuntimeException;

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
     * @var CommitFilter
     */
    private $filter;

    public function __construct(PackageHistory $history, GithubClient $client, CommitFilter $filter)
    {
        $this->history = $history;
        $this->client = $client;
        $this->parser = new GithubUrlParser();
        $this->filter = $filter;
    }

    public function getIterator()
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
            throw new RuntimeException(sprintf(
                'Unexpected response from Github: "%s"',
                json_encode($response, JSON_PRETTY_PRINT)
            ));
        }

        foreach ($response['commits'] as $commit) {
            yield Change::fromRawDateAndMessage(
                $commit['commit']['author']['date'],
                $commit['commit']['message']
            )->withParents(
                $commit['parents']
            );
        }
    }
}
