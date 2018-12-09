<?php

namespace DTL\WhatChanged\Adapter\Github;

interface GithubClient
{
    public function request(string $uri): array;
}
