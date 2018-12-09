<?php

namespace DTL\WhatChanged\Tests\Unit\Adapter\Github;

use DTL\WhatChanged\Adapter\Github\Client\CurlGithubClient;
use DTL\WhatChanged\Adapter\Github\GithubChangelog;
use DTL\WhatChanged\Model\PackageHistory;
use PHPUnit\Framework\TestCase;

class GithubChangelogTest extends TestCase
{
    public function testChangelog()
    {
        $history = new PackageHistory('phpactor/phpactor', 'git', 'https://github.com/phpactor/phpactor.git');
        $history->addReference('f5da23415533deed92fe3522da7099dccccf0ff0');
        $history->addReference('ccce1891f76c28faa4b0f10b67b5b7ed52eb91ad');
        $client = new CurlGithubClient();
        $changelog = new GithubChangelog($history, $client);
        $changes = iterator_to_array($changelog);

        $this->assertCount(5, $changes);
    }
}
