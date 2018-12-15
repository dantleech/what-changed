<?php

namespace DTL\WhatChanged;

use DTL\WhatChanged\Adapter\Github\GithubChangelogFactory;
use DTL\WhatChanged\Model\ChangelogFactory;
use DTL\WhatChanged\Model\ComposerLockArchiver;
use DTL\WhatChanged\Model\Filesystem;
use DTL\WhatChanged\Model\Filter;
use DTL\WhatChanged\Model\HistoryCompiler;
use DTL\WhatChanged\Model\PackageHistories;
use DTL\WhatChanged\Adapter\Symfony\Report\ConsoleReport;

final class WhatChangedContainer
{
    /**
     * @var string
     */
    private $lockFilePath;

    /**
     * @var string
     */
    private $compareLockFilePath;

    /**
     * @var string
     */
    private $cachePath;

    public function __construct(
        string $lockFilePath,
        string $compareLockFilePath,
        string $cachePath
    ) {
        $this->lockFilePath = $lockFilePath;
        $this->compareLockFilePath = $compareLockFilePath;
        $this->cachePath = $cachePath;
    }

    public function histories(): PackageHistories
    {
        return (new HistoryCompiler(
            $this->filesystem(),
            $this->lockFilePath,
            $this->compareLockFilePath,
            $this->filter()
        ))->compile();
    }

    public function changelogFactory(): ChangelogFactory
    {
        return new GithubChangelogFactory($this->cachePath);
    }

    public function filesystem(): Filesystem
    {
        return new Filesystem();
    }

    public function archiver(): ComposerLockArchiver
    {
        return new ComposerLockArchiver(
            $this->filesystem(),
            $this->lockFilePath,
            $this->compareLockFilePath
        );
    }

    public function consoleReport(): ConsoleReport
    {
        return new ConsoleReport(
            $this->histories(),
            $this->changelogFactory()
        );
    }

    private function filter(): Filter
    {
        return new Filter();
    }
}
