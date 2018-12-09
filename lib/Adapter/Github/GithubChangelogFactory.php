<?php

namespace DTL\WhatChanged\Adapter\Github;

use DTL\WhatChanged\Adapter\Github\Client\CachedGithubClient;
use DTL\WhatChanged\Adapter\Github\Client\CurlGithubClient;
use DTL\WhatChanged\Model\ChangelogFactory;
use DTL\WhatChanged\Model\PackageHistory;

class GithubChangelogFactory implements ChangelogFactory
{
    /**
     * @var string
     */
    private $cachePath;

    public function __construct(string $cachePath)
    {
        $this->cachePath = $cachePath;
    }

    public function changeLogFor(PackageHistory $history)
    {
        return new GithubChangelog($history, $this->createClient());
    }

    private function createClient()
    {
        $client = new CurlGithubClient();
        $client = new CachedGithubClient($client, $this->cachePath);

        return $client;
    }
}
