<?php

namespace DTL\WhatChanged\Model\Output;

use DTL\WhatChanged\Model\ReportOutput;

class BufferedReportOutput implements ReportOutput
{
    private $buffer = '';

    public function write(string $output)
    {
        $this->buffer .= $output;
    }

    public function writeln(?string $output = null)
    {
        $this->write($output . PHP_EOL);
    }

    public function __toString()
    {
        return $this->buffer;
    }
}
