<?php

namespace DTL\WhatChanged;

use DTL\WhatChanged\Adapter\Github\GithubChangelogFactory;
use DTL\WhatChanged\Model\ChangelogFactory;
use DTL\WhatChanged\Model\ComposerLockArchiver;
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

    public function __construct(string $cwd, int $limit)
    {
        $this->cwd = $cwd;
        $this->limit = $limit;
    }

    public function histories(): PackageHistories
    {
        return (new HistoryCompiler($this->lockFiles()))->compile();
    }

    public function changelogFactory(): ChangelogFactory
    {
        return new GithubChangelogFactory($this->archiver()->archivePath());
    }

    public function archiver(): ComposerLockArchiver
    {
        return new ComposerLockArchiver($this->cwd);
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
            $this->archiver()->archivePath(),
            $this->cwd,
            $this->limit
        );
    }
}
