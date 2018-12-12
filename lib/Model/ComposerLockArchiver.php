<?php

namespace DTL\WhatChanged\Model;

use DTL\WhatChanged\Model\Exception\CouldNotArchiveComposerLock;

class ComposerLockArchiver
{
    /**
     * @var string
     */
    private $lockFilePath;

    /**
     * @var string
     */
    private $compareLockFilePath;

    public function __construct(string $lockFilePath, string $compareLockFilePath)
    {
        $this->lockFilePath = $lockFilePath;
        $this->compareLockFilePath = $compareLockFilePath;
    }

    public function archive(): void
    {
        $lockFilePath = $this->resolveLockFilePath();

        if (null === $lockFilePath) {
            return;
        }

        if (!file_exists(dirname($this->compareLockFilePath))) {
            mkdir(dirname($this->compareLockFilePath), 0777, true);
        }

        if (copy($lockFilePath, $this->compareLockFilePath)) {
            return;
        }

        throw new CouldNotArchiveComposerLock(sprintf(
            'Could not archive composer lock file from "%s" to "%s"',
            $lockFilePath,
            $this->compareLockFilePath
        ));
    }

    private function resolveLockFilePath(): ?string
    {
        $candidate = $this->lockFilePath . DIRECTORY_SEPARATOR . 'composer.lock';
        if (file_exists($candidate)) {
            return $candidate;
        }

        return null;
    }
}
