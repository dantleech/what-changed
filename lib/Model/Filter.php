<?php

namespace DTL\WhatChanged\Model;

class Filter
{
    public function isValid(array $package): bool
    {
        $source = $package['source'];
        // we only support git currently
        if ($source['type'] !== 'git') {
            return false;
        }

        // we only support github
        if (false === strpos($source['url'], 'https://github.com/')) {
            return false;
        }

        return true;
    }
}
