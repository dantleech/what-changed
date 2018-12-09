<?php

namespace DTL\WhatChanged\Adapter\Composer;

use Composer\IO\IOInterface;
use DTL\WhatChanged\Model\ReportOutput;

class ComposerReportOutput implements ReportOutput
{
    /**
     * @var IOInterface
     */
    private $output;

    public function __construct(IOInterface $output)
    {
        $this->output = $output;
    }

    public function write(string $output): void
    {
        $this->output->write($output);
    }

    public function writeln(?string $output = null): void
    {
        if (null === $output) {
            $this->output->write(PHP_EOL);
            return;
        }
        $this->output->write($output);
    }
}
