<?php

namespace DTL\WhatChanged\Model;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class PackageHistories implements IteratorAggregate, Countable
{
    private $histories = [];

    public function __construct(array $histories)
    {
        foreach ($histories as $history) {
            $this->add($history);
        }
    }

    private function add(PackageHistory $history)
    {
        $this->histories[] = $history;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->histories);
    }

    public function changed()
    {
        return array_filter($this->histories, function (PackageHistory $history) {
            return $history->hasChanged();
        });
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->histories);
    }
}
