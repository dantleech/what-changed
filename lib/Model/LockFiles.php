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

    /**
     * @var int
     */
    private $limit;

    public function __construct(string $archivePath, string $projectPath, int $limit)
    {
        $this->archivePath = $archivePath;
        $this->projectPath = $projectPath;
        $this->limit = $limit;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        $files = [];

        if (file_exists($this->archivePath)) {
            $files = new FilesystemIterator($this->archivePath);
            $files = new CallbackFilterIterator($files, function (SplFileInfo $info) {
                return $info->isFile() && $info->getExtension() === 'lock';
            });
            $files = $this->sort($files);
            $files = array_slice($files, -$this->limit);
            var_dump($files);
        }


        if (file_exists($this->composerLockPath())) {
            $files[] = new SplFileInfo($this->composerLockPath());
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
