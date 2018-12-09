<?php

namespace DTL\WhatChanged\Tests\Unit;

use DTL\WhatChanged\Model\HistoryCompiler;
use DTL\WhatChanged\Model\LockFiles;
use PHPUnit\Framework\TestCase;

class HistoryCompilerTest extends TestCase
{
    /**
     * @var HistoryCompiler
     */
    private $compiler;

    public function setUp()
    {
        $this->compiler = new HistoryCompiler(
            new LockFiles(__DIR__ . '/../Example', '')
        );
    }

    public function testCompile()
    {
        $histories = $this->compiler->compile();
        $this->assertCount(18, $histories);
        $this->assertCount(1, $histories->changed());
    }
}
