<?php

namespace DTL\WhatChanged\Model;

use RuntimeException;
use SplFileInfo;
use Symfony\Component\Finder\Iterator\SortableIterator;

class HistoryCompiler
{
    /**
     * @var LockFiles<SplFileInfo>
     */
    private $files;

    public function __construct(LockFiles $files)
    {
        $this->files = $files;
    }

    public function compile(): PackageHistories
    {
        $packageHistories = [];

        foreach ($this->files as $file) {
            $lock = $this->loadFile($file);
            foreach ($lock['packages'] as $package) {
                if (!isset($package['source'])) {
                    continue;
                }

                $source = $package['source'];

                // we only support git currently
                if ($source['type'] !== 'git') {
                    continue;
                }

                // we only support github
                if (false === strpos($source['url'], 'https://github.com/')) {
                    continue;
                }

                if (!isset($packageHistories[$package['name']])) {
                    $packageHistories[$package['name']] = new PackageHistory(
                        $package['name'],
                        $source['type'],
                        $source['url']
                    );
                }

                $packageHistories[$package['name']]->addReference($source['reference']);
            }
        }

        return new PackageHistories($packageHistories);
    }

    private function loadFile(SplFileInfo $file): array
    {
        $contents = file_get_contents($file->getPathname());

        if (false === $contents) {
            throw new RuntimeException(sprintf(
                'Could not read file "%s"', $file->getPathname()
            ));
        }

        return json_decode($contents, true);
    }
}
