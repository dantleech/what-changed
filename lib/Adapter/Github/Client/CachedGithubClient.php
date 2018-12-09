<?php

namespace DTL\WhatChanged\Adapter\Github\Client;

use DTL\WhatChanged\Adapter\Github\GithubClient;

class CachedGithubClient implements GithubClient
{
    /**
     * @var GithubClient
     */
    private $client;

    /**
     * @var string
     */
    private $cachePath;

    public function __construct(GithubClient $client, string $cachePath)
    {
        $this->client = $client;
        $this->cachePath = $cachePath;
    }

    public function request(string $uri): array
    {
        $uriHash = md5($uri);
        $path = sprintf('%s/%s.cache', $this->cachePath, md5($uri));

        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true);
        }

        $response = $this->client->request($uri);
        file_put_contents($path, json_encode($response, JSON_PRETTY_PRINT));

        return $response;
    }
}
