<?php

namespace DTL\WhatChanged\Model;

use DTL\WhatChanged\Exception\WhatChangedRuntimeException;
use RuntimeException;

class Filesystem
{
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    public function copy(string $sourcePath, string $targetPath): void
    {
        $this->ensureDirectoryExists(dirname($targetPath));

        if (copy($sourcePath, $targetPath)) {
            return;
        }

        throw new WhatChangedRuntimeException(sprintf(
            'Could not copy file "%s" to "%s"', $sourcePath, $targetPath
        ));
    }

    private function ensureDirectoryExists(string $path): void
    {
        if (file_exists($path)) {
            return;
        }

        if (mkdir($path, 0777, true)) {
            return;
        }

        throw new WhatChangedRuntimeException(sprintf(
            'Could not create directory "%s"', $path
        ));
    }

    public function getContents(string $path)
    {
        $this->assertFileExists($path);
        $contents = file_get_contents($path);

        if (false === $contents) {
            throw new WhatChangedRuntimeException(sprintf(
                'Could not read file "%s"',
                $path
            ));
        }

        return $contents;
    }

    private function assertFileExists(string $path): void
    {
        if (file_exists($path)) {
            return;
        }

        throw new WhatChangedRuntimeException(sprintf(
            'File "%s" does not exist', $path
        ));
    }
}
