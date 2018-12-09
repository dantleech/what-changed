<?php

namespace DTL\WhatChanged\Tests\Unit\Model;

use DTL\WhatChanged\Model\Filter;
use DTL\WhatChanged\Model\HistoryCompiler;
use DTL\WhatChanged\Model\LockFiles;
use PHPUnit\Framework\TestCase;
use Phpactor\TestUtils\Workspace;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class HistoryCompilerTest extends TestCase
{
    /**
     * @var Workspace
     */
    private $workspace;

    /**
     * @var ObjectProphecy&Filter
     */
    private $filter;

    public function setUp()
    {
        $this->workspace = Workspace::create(__DIR__ . '/../../Workspace');
        $this->workspace->reset();
        $this->workspace->mkdir('archive');
        $this->filter = $this->prophesize(Filter::class);
        $this->filter->isValid(Argument::any())->willReturn(true);
    }

    public function testNoLockFiles()
    {
        $compiler = $this->createCompiler($this->createLock([]));
        $histories = $compiler->compile();
        $this->assertCount(0, $histories);
    }

    public function testNoChangeWithOneLock()
    {
        $compiler = $this->createCompiler($this->createLock([
            [
                'packages' => [
                    [
                        'name' => 'hello',
                        'source' => [
                            'type' => 'git',
                            'reference' => '1234',
                            'url' => 'foo'
                        ],
                    ]
                ],
            ]
        ]));
        $histories = $compiler->compile();
        $this->assertCount(1, $histories);
    }

    public function testNoChangeWithTwoLocks()
    {
        $compiler = $this->createCompiler($this->createLock([
            [
                'packages' => [
                    [
                        'name' => 'hello',
                        'source' => [
                            'type' => 'git',
                            'reference' => '1234',
                            'url' => 'foo'
                        ],
                    ]
                ],
            ],
            [
                'packages' => [
                    [
                        'name' => 'hello',
                        'source' => [
                            'type' => 'git',
                            'reference' => '1234',
                            'url' => 'foo'
                        ],
                    ]
                ],
            ],
        ]));
        $histories = $compiler->compile();
        $this->assertCount(1, $histories);
        $this->assertFalse($histories->at(0)->hasChanged());
        $this->assertFalse($histories->at(0)->isNew());
    }

    public function testDetectsUpgradeInOnePackage()
    {
        $compiler = $this->createCompiler($this->createLock([
            [
                'packages' => [
                    [
                        'name' => 'hello',
                        'source' => [
                            'type' => 'git',
                            'reference' => '1234',
                            'url' => 'foo'
                        ],
                    ]
                ],
            ],
            [
                'packages' => [
                    [
                        'name' => 'hello',
                        'source' => [
                            'type' => 'git',
                            'reference' => '456',
                            'url' => 'foo'
                        ],
                    ]
                ],
            ],
        ]));
        $histories = $compiler->compile();
        $this->assertCount(1, $histories);
        $this->assertTrue($histories->at(0)->hasChanged());
        $this->assertFalse($histories->at(0)->isNew());
    }

    public function testDetectsNewPackage()
    {
        $compiler = $this->createCompiler($this->createLock([
            [
                'packages' => [
                    [
                        'name' => 'hello',
                        'source' => [
                            'type' => 'git',
                            'reference' => '1234',
                            'url' => 'foo'
                        ],
                    ]
                ],
            ],
            [
                'packages' => [
                    [
                        'name' => 'hello',
                        'source' => [
                            'type' => 'git',
                            'reference' => '1234',
                            'url' => 'foo'
                        ],
                    ],
                    [
                        'name' => 'goodbye',
                        'source' => [
                            'type' => 'git',
                            'reference' => '456',
                            'url' => 'foo'
                        ],
                    ]
                ],
            ],
        ]));
        $histories = $compiler->compile();
        $this->assertCount(2, $histories);
        $this->assertTrue($histories->at(1)->hasChanged());
        $this->assertTrue($histories->at(1)->isNew());
    }

    private function createCompiler(LockFiles $lockFiles): HistoryCompiler
    {
        return new HistoryCompiler($lockFiles, $this->filter->reveal());
    }

    private function createLock(array $data)
    {
        foreach ($data as $index => $lockFile) {
            file_put_contents($this->workspace->path('archive/lock'.$index.'.lock'), json_encode(array_replace_recursive([
                'packages' => [],
            ], $lockFile)));
        }
        return new LockFiles(
            $this->workspace->path('archive'),
            $this->workspace->path(''),
            2
        );
    }
}
