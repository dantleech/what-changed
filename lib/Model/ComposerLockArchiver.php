<?php

namespace DTL\WhatChanged\Model;

use DTL\WhatChanged\Exception\WhatChangedRuntimeException;
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

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(Filesystem $filesystem, string $lockFilePath, string $compareLockFilePath)
    {
        $this->lockFilePath = $lockFilePath;
        $this->compareLockFilePath = $compareLockFilePath;
        $this->filesystem = $filesystem;
    }

    public function archive(): void
    {
        if (!$this->filesystem->exists($this->lockFilePath)) {
            return;
        }

        $e = null;

        try {
            $this->filesystem->copy($this->lockFilePath, $this->compareLockFilePath);
            return;
        } catch (WhatChangedRuntimeException $e) {
        }

        throw new CouldNotArchiveComposerLock(sprintf(
            'Could not archive composer lock file from "%s" to "%s"',
            $this->lockFilePath,
            $this->compareLockFilePath
        ), 0, $e);
    }
}
