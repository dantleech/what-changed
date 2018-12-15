<?php

namespace DTL\WhatChanged\Model;

use DateTimeImmutable;

class ChangeBuilder
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
     * @var array
     */
    private $parents;

    /**
     * @var string
     */
    private $sha;

    /**
     * @var string
     */
    private $author;

    final private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function rawDate(string $date): self
    {
        $this->date = new DateTimeImmutable($date);
        return $this;
    }

    public function message(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function parents(array $parents): self
    {
        $this->parents = $parents;
        return $this;
    }

    public function sha(string $sha): self
    {
        $this->sha = $sha;
        return $this;
    }

    public function author(string $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function build(): Change
    {
        return new Change(
            $this->date,
            $this->message,
            $this->sha,
            $this->parents,
            $this->author
        );
    }
}
