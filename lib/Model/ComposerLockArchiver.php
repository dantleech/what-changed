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

    /**
     * @var string
     */
    private $archivePath;

    public function __construct(string $projectDirectory, string $archivePath)
    {
        $this->projectDirectory = $projectDirectory;
        $this->archivePath = $archivePath;
    }

    public function archive(): void
    {
        $lockFilePath = $this->resolveLockFilePath();

        if (null === $lockFilePath) {
            return;
        }

        if (!file_exists($this->archivePath)) {
            mkdir($this->archivePath, 0777, true);
        }

        if (copy($lockFilePath, $this->resolvePath())) {
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

    private function resolvePath()
    {
        return implode(DIRECTORY_SEPARATOR, [
            $this->archivePath,
            date(self::ARCHIVE_FORMAT) . '.lock'
        ]);
    }
}
