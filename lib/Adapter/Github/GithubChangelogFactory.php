<?php

namespace DTL\WhatChanged\Adapter\Github;

use DTL\WhatChanged\Adapter\Github\Client\CachedGithubClient;
use DTL\WhatChanged\Adapter\Github\Client\CurlGithubClient;
use DTL\WhatChanged\Model\Changelog;
use DTL\WhatChanged\Model\ChangelogFactory;
use DTL\WhatChanged\Model\PackageHistory;

class GithubChangelogFactory implements ChangelogFactory
{
    /**
     * @var string
     */
    private $cachePath;

    /**
     * @var string|null
     */
    private $oauthToToken;

    public function __construct(string $cachePath, ?string $oauthToToken = null)
    {
        $this->cachePath = $cachePath;
        $this->oauthToToken = $oauthToToken;
    }

    public function changeLogFor(PackageHistory $history): Changelog
    {
        return new GithubChangelog($history, $this->createClient());
    }

    private function createClient()
    {
        $client = new CurlGithubClient($this->oauthToToken);
        $client = new CachedGithubClient($client, $this->cachePath);

        return $client;
    }
}
