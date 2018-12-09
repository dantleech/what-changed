<?php

namespace DTL\WhatChanged;

use DTL\WhatChanged\Adapter\Github\GithubChangelogFactory;
use DTL\WhatChanged\Model\ChangelogFactory;
use DTL\WhatChanged\Model\ComposerLockArchiver;
use DTL\WhatChanged\Model\HistoryCompiler;
use DTL\WhatChanged\Model\LockFiles;
use DTL\WhatChanged\Model\PackageHistories;

final class WhatChangedContainer
{
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
        return new ComposerLockArchiver(getcwd());
    }

    public function lockFiles()
    {
        return new LockFiles(
            $this->archiver()->archivePath(),
            getcwd()
        );
    }
}
