<?php

namespace DTL\WhatChanged\Tests\Integration\Model;

use DTL\WhatChanged\Model\Filesystem;
use PHPUnit\Framework\TestCase;
use Phpactor\TestUtils\Workspace;

class FilesystemTest extends TestCase
{
    /**
     * @var Filesystem
     */
    private $filesystsem;

    /**
     * @var Workspace
     */
    private $workspace;

    public function setUp()
    {
        $this->filesystsem = new Filesystem();
        $this->workspace = Workspace::create(__DIR__ . '/../../Workspace');
        $this->workspace->reset();
    }

    public function testFileExists()
    {
        $this->assertTrue($this->filesystsem->exists(__DIR__));
        $this->assertFalse($this->filesystsem->exists(__DIR__ . '/no'));
        $this->assertTrue($this->filesystsem->exists(__FILE__));
    }

    public function testCopyToNonExistingDirectory()
    {
        $targetPath = $this->workspace->path('new-directory/foo.php');
        $this->filesystsem->copy(__FILE__, $targetPath);
        $this->assertTrue(file_exists($targetPath));
        $this->assertEquals(file_get_contents(__FILE__), file_get_contents($targetPath));
    }

    public function testCopyToExistingDirectory()
    {
        $targetPath = $this->workspace->path('new-directory');
        $this->filesystsem->copy(__FILE__, $targetPath);
        $this->assertTrue(file_exists($targetPath));
        $this->assertEquals(file_get_contents(__FILE__), file_get_contents($targetPath));
    }
}
