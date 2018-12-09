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
    private $cwd;

    public function __construct($cwd)
    {
        $this->cwd = $cwd;
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
            $this->changelogFactory()
        );
    }

    public function lockFiles()
    {
        return new LockFiles(
            $this->archiver()->archivePath(),
            $this->cwd
        );
    }
}
