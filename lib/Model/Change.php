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

    public function __construct(DateTimeImmutable $date, string $message)
    {
        $this->date = $date;
        $this->message = $message;
    }

    public static function fromRawDateAndMessage($argument0, $argument1)
    {
        return new self(new DateTimeImmutable($argument0), $argument1);
    }

    public function date(): DateTimeImmutable
    {
        return $this->date;
    }

    public function message(): string
    {
        return $this->message;
    }
}
