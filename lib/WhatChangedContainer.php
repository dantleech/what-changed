<?php

namespace DTL\WhatChanged;

use DTL\WhatChanged\Adapter\Github\GithubChangelogFactory;
use DTL\WhatChanged\Model\ChangelogFactory;
use DTL\WhatChanged\Model\ComposerLockArchiver;
use DTL\WhatChanged\Model\Filter;
use DTL\WhatChanged\Model\HistoryCompiler;
use DTL\WhatChanged\Model\LockFiles;
use DTL\WhatChanged\Model\PackageHistories;
use DTL\WhatChanged\Report\ConsoleReport;

final class WhatChangedContainer
{
    /**
     * @var string
     */
    private $cwd;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var string
     */
    private $archivePath;

    public function __construct(
        string $cwd,
        int $limit,
        string $archivePath
    ) {
        $this->cwd = $cwd;
        $this->limit = $limit;
        $this->archivePath = $archivePath;
    }

    public function histories(): PackageHistories
    {
        return (new HistoryCompiler(
            $this->lockFiles(),
            $this->filter()
        ))->compile();
    }

    public function changelogFactory(): ChangelogFactory
    {
        return new GithubChangelogFactory($this->archivePath);
    }

    public function archiver(): ComposerLockArchiver
    {
        return new ComposerLockArchiver($this->cwd, $this->archivePath);
    }

    public function consoleReport(): ConsoleReport
    {
        return new ConsoleReport(
            $this->histories(),
            $this->changelogFactory()
        );
    }

    public function lockFiles()
    {
        return new LockFiles(
            $this->archivePath,
            $this->cwd,
            $this->limit
        );
    }

    private function filter(): Filter
    {
        return new Filter();
    }
}
