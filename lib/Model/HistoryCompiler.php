<?php

namespace DTL\WhatChanged\Model;

use RuntimeException;
use SplFileInfo;

class HistoryCompiler
{
    /**
     * @var LockFiles<SplFileInfo>
     */
    private $files;

    /**
     * @var Filter
     */
    private $filter;

    public function __construct(LockFiles $files, Filter $filter)
    {
        $this->files = $files;
        $this->filter = $filter;
    }

    public function compile(): PackageHistories
    {
        $packageHistories = [];

        foreach ($this->files as $index => $file) {
            $lock = $this->loadFile($file);

            foreach ($lock['packages'] as $package) {

                if (!$this->filter->isValid($package)) {
                    continue;
                }

                if (!isset($package['source'])) {
                    continue;
                }

                $source = $package['source'];

                $isNew = false;
                if (!isset($packageHistories[$package['name']])) {
                    $packageHistories[$package['name']] = new PackageHistory(
                        $package['name'],
                        $source['type'],
                        $source['url']
                    );
                    $isNew = true;
                }

                /** @var PackageHistory $packageHistory */
                $packageHistory = $packageHistories[$package['name']];

                // if this is the first time the package has been seen (and
                // this is not the first iteration) then it has been added.
                if ($isNew && $index > 0) {
                    $packageHistory->markAsNew();
                }

                $packageHistory->addReference($source['reference']);
            }
        }

        return new PackageHistories($packageHistories);
    }

    private function loadFile(SplFileInfo $file): array
    {
        $contents = file_get_contents($file->getPathname());

        if (false === $contents) {
            throw new RuntimeException(sprintf(
                'Could not read file "%s"',
                $file->getPathname()
            ));
        }

        return json_decode($contents, true);
    }
}
