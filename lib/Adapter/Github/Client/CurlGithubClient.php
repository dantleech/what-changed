<?php

namespace DTL\WhatChanged\Adapter\Github\Client;

use DTL\WhatChanged\Adapter\Github\Exception\GithubClientException;
use DTL\WhatChanged\Adapter\Github\GithubClient;

class CurlGithubClient implements GithubClient
{
    /**
     * @var string
     */
    private $oauthToken;

    public function __construct(?string $oauthToken)
    {
        $this->oauthToken = $oauthToken;
    }

    public function request(string $uri): array
    {
        $ch = curl_init($uri);
        if (false === $ch) {
            throw new GithubClientException(sprintf(
                'Could not initialize curl for URI "%s"',
                $uri
            ));
        }

        if ($this->oauthToken) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: token ' . $this->oauthToken
            ]);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Composer What Changed');

        $rawResponse = curl_exec($ch);
        $response = $this->decodeResponse((string) $rawResponse);

        return $response;
    }

    private function decodeResponse(string $response)
    {
        $data = json_decode($response, true);

        return $data;
    }
}
