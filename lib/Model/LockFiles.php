<?php

namespace DTL\WhatChanged\Model;

use ArrayIterator;
use CallbackFilterIterator;
use FilesystemIterator;
use Iterator;
use IteratorAggregate;
use SplFileInfo;

class LockFiles implements IteratorAggregate
{
    /**
     * @var string
     */
    private $archivePath;

    /**
     * @var string
     */
    private $projectPath;

    public function __construct(string $archivePath, string $projectPath)
    {
        $this->archivePath = $archivePath;
        $this->projectPath = $projectPath;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        $files = new FilesystemIterator($this->archivePath);
        $files = new CallbackFilterIterator($files, function (SplFileInfo $info) {
            return $info->isFile() && $info->getExtension() === 'lock';
        });
        $files = $this->sort($files);

        if (file_exists($this->composerLockPath())) {
            array_unshift($files, new SplFileInfo($this->composerLockPath()));
        }

        return new ArrayIterator($files);
    }

    private function sort(Iterator $files): array
    {
        $files = iterator_to_array($files);
        usort($files, function (SplFileInfo $a, SplFileInfo $b) {
            return $a->getFilename() <=> $b->getFilename();
        });

        return $files;
    }

    private function composerLockPath(): string
    {
        return implode(DIRECTORY_SEPARATOR, [ $this->projectPath, 'composer.lock' ]);
    }
}
