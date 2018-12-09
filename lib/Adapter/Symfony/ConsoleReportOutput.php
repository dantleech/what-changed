<?php

namespace DTL\WhatChanged\Adapter\Symfony;

use DTL\WhatChanged\Model\ReportOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleReportOutput implements ReportOutput
{
    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
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
        $this->output->writeln($output);
    }
}
