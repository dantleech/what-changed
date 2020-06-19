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

    /**
     * @var int|null
     */
    private $maxCommits;

    public function __construct(string $cachePath, ?string $oauthToToken = null, ?int $maxCommits = null)
    {
        $this->cachePath = $cachePath;
        $this->oauthToToken = $oauthToToken;
        $this->maxCommits = $maxCommits;
    }

    public function changeLogFor(PackageHistory $history): Changelog
    {
        return new GithubChangelog($history, $this->createClient(), $this->maxCommits);
    }

    private function createClient()
    {
        $client = new CurlGithubClient($this->oauthToToken);
        $client = new CachedGithubClient($client, $this->cachePath);

        return $client;
    }
}
