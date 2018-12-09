<?php

namespace DTL\WhatChanged\Adapter\Github;

use DTL\WhatChanged\Model\ChangelogFactory;
use DTL\WhatChanged\Model\PackageHistory;
use GuzzleHttp\Client;

class GithubChangelogFactory implements ChangelogFactory
{
    public function changeLogFor(PackageHistory $history)
    {
        return new GithubChangelog($history);
    }
}
