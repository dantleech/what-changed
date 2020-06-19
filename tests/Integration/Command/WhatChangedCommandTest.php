<?php

namespace DTL\WhatChanged\Tests\Integration\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class WhatChangedCommandTest extends TestCase
{
    public function testWhatChanged()
    {
        $process = $this->runCommand();
        $this->assertSuccess($process);
    }

    public function testWithMergeCommits()
    {
        return;
        $process = $this->runCommand(['--merge-commits']);
        $this->assertSuccess($process);
        $this->assertContains('Merge', $process->getOutput());
    }

    public function testWithFullMessage()
    {
        return;
        $process = $this->runCommand(['--full-message']);
        $this->assertSuccess($process);
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

    private function assertSuccess(Process $process)
    {
        if (false === $process->isSuccessful()) {
            echo $process->getOutput();
            echo $process->getErrorOutput();
        }
        $this->assertEquals(0, $process->getExitCode());
    }
}
