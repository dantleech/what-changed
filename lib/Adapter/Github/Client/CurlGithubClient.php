<?php

namespace DTL\WhatChanged\Adapter\Github\Client;

use DTL\WhatChanged\Adapter\Github\GithubClient;

class CurlGithubClient implements GithubClient
{
    public function request(string $uri): array
    {
        $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Composer What Changed');
        $rawResponse = curl_exec($ch);
        $response = $this->decodeResponse($rawResponse);

        return $response;
    }

    private function decodeResponse(string $response)
    {
        $data = json_decode($response, true);

        return $data;
    }
}
