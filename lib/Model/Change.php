<?php

namespace DTL\WhatChanged\Model;

use DateTimeImmutable;

class Change
{
    /**
     * @var DateTimeImmutable
     */
    private $date;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string[]
     */
    private $parents = [];

    /**
     * @var string
     */
    private $sha;

    /**
     * @var string
     */
    private $author;

    public function __construct(
        DateTimeImmutable $date,
        string $message,
        string $sha,
        array $parents,
        string $author
    ) {
        $this->date = $date;
        $this->message = $message;
        $this->sha = $sha;
        $this->parents = $parents;
        $this->author = $author;
    }

    public function date(): DateTimeImmutable
    {
        return $this->date;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function isMerge(): bool
    {
        return count($this->parents) > 1;
    }

    public function sha(): string
    {
        return $this->sha;
    }

    public function author(): string
    {
        return $this->author;
    }
}
