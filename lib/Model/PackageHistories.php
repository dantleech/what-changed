<?php

namespace DTL\WhatChanged\Model;

use ArrayIterator;
use Countable;
use DTL\WhatChanged\Exception\WhatChangedRuntimeException;
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

    public function at(int $index): PackageHistory
    {
        if (!isset($this->histories[$index])) {
            throw new WhatChangedRuntimeException(sprintf(
                'Unknown index "%s"', $index
            ));
        }

        return $this->histories[$index];
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

    public function changed(): self
    {
        return new self(array_filter($this->histories, function (PackageHistory $history) {
            return $history->hasChanged();
        }));
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->histories);
    }

    public function tail(int $limit): self
    {
        return new self(array_slice($this->histories, -$limit));
    }
}
