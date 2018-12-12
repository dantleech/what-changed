<?php

namespace DTL\WhatChanged\Model;

use RuntimeException;

class HistoryCompiler
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var string
     */
    private $compareLockFilePath;

    /**
     * @var string
     */
    private $lockFilePath;

    public function __construct(
        string $lockFilePath,
        string $compareLockFilePath,
        Filter $filter
    ) {
        $this->filter = $filter;
        $this->compareLockFilePath = $compareLockFilePath;
        $this->lockFilePath = $lockFilePath;
    }

    public function compile(): PackageHistories
    {
        $packageHistories = [];

        $files = [
            $this->compareLockFilePath,
            $this->lockFilePath,
        ];

        $files = array_filter($files, function (string $path) {
            return file_exists($path);
        });

        $files = array_map(function (string $path) {
            return $this->loadFile($path);
        }, $files);

        if (count($files) > 1) {
            $packageHistories = $this->buildHistories($files, $packageHistories);
        }

        return new PackageHistories($packageHistories);
    }

    private function loadFile(string $path): array
    {
        $contents = file_get_contents($path);

        if (false === $contents) {
            throw new RuntimeException(sprintf(
                'Could not read file "%s"',
                $path
            ));
        }

        $decoded = json_decode($contents, true);

        if (null === $decoded) {
            throw new RuntimeException(sprintf(
                'Could not decode JSON file "%s": %s',
                $path,
                json_last_error_msg()
            ));
        }

        return $decoded;
    }

    private function packagesFromLock(array $lock): array
    {
        $packages = [];
            
        if (isset($lock['packages'])) {
            $packages = array_merge($packages, $lock['packages']);
        }
        
        if (isset($lock['packages-dev'])) {
            $packages = array_merge($packages, $lock['packages-dev']);
        }

        return $packages;
    }

    private function buildHistories(array $files, array $packageHistories)
    {
        foreach ($files as $index => $file) {
            $lockPackageNames = [];
            $packages = $this->packagesFromLock($file);
        
            foreach ($packages as $package) {
                if (!$this->filter->isValid($package)) {
                    continue;
                }
        
                if (!isset($package['source'])) {
                    continue;
                }
        
                $packageName = $package['name'];
                $lockPackageNames[] = $packageName;
        
                $source = $package['source'];
        
                $isNew = false;
                if (!isset($packageHistories[$packageName])) {
                    $packageHistories[$packageName] = new PackageHistory(
                        $packageName,
                        $source['type'],
                        $source['url']
                    );
                    $isNew = true;
                }
        
                /** @var PackageHistory $packageHistory */
                $packageHistory = $packageHistories[$packageName];
        
                // if this is the first time the package has been seen (and
                // this is not the first iteration) then it has been added.
                if (($isNew || $packageHistory->isRemoved()) && $index > 0) {
                    $packageHistory->markAsNew();
                }
        
                $packageHistory->addReference($source['reference']);
            }
        
            foreach (array_diff(array_keys($packageHistories), $lockPackageNames) as $removedPackageName) {
                $packageHistories[$removedPackageName]->markAsRemoved();
            }
        }

        return $packageHistories;
    }
}
