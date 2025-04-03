<?php

namespace DTL\WhatChanged\Model;

interface ReportOutput
{
    public function write(string $output);
    public function writeln(?string $output = null);
}
