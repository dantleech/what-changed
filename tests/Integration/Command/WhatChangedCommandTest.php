<?php

namespace DTL\WhatChanged\Tests\Integration\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class WhatChangedCommandTest extends TestCase
{
    public function testWhatChanged()
    {
        $process = $this->runCommand();
        $this->assertEquals(0, $process->getExitCode());
    }

    public function testWithMergeCommits()
    {
        $process = $this->runCommand(['--merge-commits']);
        $this->assertEquals(0, $process->getExitCode());
        $this->assertContains('Merge', $process->getOutput());
    }

    public function testWithFullMessage()
    {
        $process = $this->runCommand(['--full-message']);
        $this->assertEquals(0, $process->getExitCode());
        $this->assertContains('what the user has inputed', $process->getOutput());
    }

    private function runCommand($args = []): Process
    {
        $args = array_merge([
            'php', 'bin/test', 'what-changed'
        ], $args);
        $process = new Process($args);
        $process->setEnv(['COLUMNS' => 100]);
        $process->run();

        return $process;
    }
}
