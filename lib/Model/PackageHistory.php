<?php

namespace DTL\WhatChanged\Model;

use Countable;
use DTL\WhatChanged\Exception\WhatChangedRuntimeException;

class PackageHistory implements Countable
{
    const STATE_NEW = 'new';
    const STATE_REMOVED = 'removed';
    const STATE_UPDATED = 'updated';

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $url;

    /**
     * @var array<string>
     */
    private $references = [];

    /**
     * @var array
     */
    private $ordered = [];

    /**
     * @var string
     */
    private $state = self::STATE_UPDATED;

    public function __construct(string $name, string $type, string $url)
    {
        $this->name = $name;
        $this->type = $type;
        $this->url = $url;
    }

    public function addReference(string $reference)
    {
        if (!isset($this->references[$reference])) {
            $this->ordered[] = $reference;
        }
        $this->references[$reference] = $reference;
    }

    public function hasChanged()
    {
        if ($this->isNew()) {
            return true;
        }

        if ($this->isRemoved()) {
            return true;
        }

        return count($this->references) > 1;
    }

    public function url()
    {
        return $this->url;
    }

    public function first(): string
    {
        if (!$this->ordered) {
            $this->throwNoReferencesException();
        }

        return reset($this->ordered);
    }

    public function last(): string
    {
        if (!$this->ordered) {
            $this->throwNoReferencesException();
        }

        return $this->ordered[count($this->ordered) - 1];
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->ordered);
    }

    private function throwNoReferencesException()
    {
        throw new WhatChangedRuntimeException(sprintf(
            'No references for package "%s", cannot access first/last',
            $this->name
        ));
    }

    public function name(): string
    {
        return $this->name;
    }

    public function markAsNew(): void
    {
        $this->state = self::STATE_NEW;
    }

    public function markAsRemoved(): void
    {
        $this->state = self::STATE_REMOVED;
    }

    public function isNew(): bool
    {
        return $this->state === self::STATE_NEW;
    }

    public function isRemoved(): bool
    {
        return $this->state === self::STATE_REMOVED;
    }

    public function isUpdated()
    {
        return $this->state === self::STATE_UPDATED;
    }
}
