<?php

namespace DTL\WhatChanged\Model;

use DTL\WhatChanged\Exception\WhatChangedRuntimeException;

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
}
