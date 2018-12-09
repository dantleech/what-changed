<?php

namespace DTL\WhatChanged\Adapter\Github;

use DTL\WhatChanged\Model\Change;
use DTL\WhatChanged\Model\Changelog;
use DTL\WhatChanged\Model\PackageHistory;
use Generator;
use GuzzleHttp\ClientInterface;
use IteratorAggregate;
use Psr\Http\Message\ResponseInterface;
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

    public function __construct(PackageHistory $history)
    {
        $this->history = $history;
        $this->parser = new GithubUrlParser();
    }

    public function getIterator()
    {
        [$org, $repo] = $this->parser->parse($this->history->url());

        $url = sprintf(
            'https://api.github.com/repos/%s/%s/compare/%s...%s',
            $org, $repo,
            $this->history->last(),
            $this->history->first()
        );
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Composer What Changed');
        $rawResponse = curl_exec($ch);
        $response = $this->decodeResponse($rawResponse);

        if (!isset($response['commits'])) {
            throw new RuntimeException(sprintf(
                'Unexpected response from Github: "%s"', $rawResponse
            ));
        }

        foreach ($response['commits'] as $commit) {
            yield Change::fromRawDateAndMessage(
                $commit['commit']['author']['date'],
                $commit['commit']['message']
            );
        }
    }

    private function decodeResponse(string $response)
    {
        $data = json_decode($response, true);

        return $data;
    }
}
