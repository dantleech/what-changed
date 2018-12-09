<?php

namespace DTL\WhatChanged\Model;

use DTL\WhatChanged\Model\Exception\CouldNotArchiveComposerLock;

class ComposerLockArchiver
{
    const ARCHIVE_FORMAT = 'YmdHis';

    /**
     * @var string
     */
    private $projectDirectory;

    public function __construct(string $projectDirectory)
    {
        $this->projectDirectory = $projectDirectory;
    }

    public function archive(): void
    {
        $lockFilePath = $this->resolveLockFilePath();

        if (null === $lockFilePath) {
            return;
        }

        $archivePath = $this->resolvePath();

        if (!file_exists(dirname($archivePath))) {
            mkdir(dirname($archivePath), 0777, true);
        }

        if (copy($lockFilePath, $archivePath)) {
            return;
        }

        throw new CouldNotArchiveComposerLock(sprintf(
            'Could not archive composer lock file from "%s" to "%s"',
            $lockFilePath,
            $archivePath
        ));
    }

    private function resolveLockFilePath(): ?string
    {
        $candidate = $this->projectDirectory . DIRECTORY_SEPARATOR . 'composer.lock';
        if (file_exists($candidate)) {
            return $candidate;
        }

        return null;
    }

    public function archivePath()
    {
        return implode(DIRECTORY_SEPARATOR, [
            $this->projectDirectory,
            'vendor',
            'composer',
            'archive'
        ]);
    }

    private function resolvePath()
    {
        return implode(DIRECTORY_SEPARATOR, [
            $this->archivePath(),
            date(self::ARCHIVE_FORMAT) . '.lock'
        ]);
    }
}
