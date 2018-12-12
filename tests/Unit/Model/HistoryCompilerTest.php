<?php

namespace DTL\WhatChanged\Tests\Unit\Model;

use DTL\WhatChanged\Model\Filter;
use DTL\WhatChanged\Model\HistoryCompiler;
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

    public function testNoFilesExisting()
    {
        $compiler = $this->createCompiler(
            $this->workspace->path('noexist1'),
            $this->workspace->path('noexist2'),
        );
        $histories = $compiler->compile();
        $this->assertCount(0, $histories);
    }

    public function testNoChangeWithOneLock()
    {
        $compiler = $this->createCompiler($this->createLock('composer_lock', [
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
        ]), $this->workspace->path('noexist2'));
        $histories = $compiler->compile();
        $this->assertCount(0, $histories);
    }

    public function testNoChangeWithTwoLocks()
    {
        $compiler = $this->createCompiler($this->createLock('composer_lock', [
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
        ]), $this->createLock('composer_lock_old', [
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
        ]));
        $histories = $compiler->compile();
        $this->assertCount(1, $histories);
        $this->assertFalse($histories->at(0)->hasChanged());
        $this->assertFalse($histories->at(0)->isNew());
    }

    public function testDetectsUpgradeInOnePackage()
    {
        $compiler = $this->createCompiler($this->createLock('composer_lock', [
            'packages' => [
                [
                    'name' => 'hello',
                    'source' => [
                        'type' => 'git',
                        'reference' => '1234',
                        'url' => 'foo'
                    ],
                ]
            ]
        ]), $this->createLock('composer_lock_old', [
            'packages' => [
                [
                    'name' => 'hello',
                    'source' => [
                        'type' => 'git',
                        'reference' => '456',
                        'url' => 'foo'
                    ],
                ]
            ]
        ]));
        $histories = $compiler->compile();
        $this->assertCount(1, $histories);
        $this->assertTrue($histories->at(0)->hasChanged());
        $this->assertFalse($histories->at(0)->isNew());
    }

    public function testDetectsNewPackage()
    {
        $compiler = $this->createCompiler($this->createLock('composer_lock', [
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
            ]
        ]), $this->createLock('composer_lock_old', [
            'packages' => [
                [
                    'name' => 'hello',
                    'source' => [
                        'type' => 'git',
                        'reference' => '1234',
                        'url' => 'foo'
                    ],
                ],
            ],
        ]));
        $histories = $compiler->compile();
        $this->assertCount(2, $histories);
        $this->assertTrue($histories->at(1)->hasChanged());
        $this->assertTrue($histories->at(1)->isNew());
    }

    public function testDetectsRemovedPackage()
    {
        $compiler = $this->createCompiler($this->createLock('composer_lock', [
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
            ]), $this->createLock('composer_lock_old', [
                'packages' => [
                    [
                        'name' => 'goodbye',
                        'source' => [
                            'type' => 'git',
                            'reference' => '456',
                            'url' => 'foo'
                        ],
                    ]
                ],
        ]));
        $histories = $compiler->compile();
        $this->assertCount(2, $histories);
        $this->assertTrue($histories->at(0)->isRemoved());
        $this->assertTrue($histories->at(0)->hasChanged());
    }

    public function testDetectsUpgradeInDevPackage()
    {
        $compiler = $this->createCompiler($this->createLock('composer_lock', [
                'packages-dev' => [
                    [
                        'name' => 'goodbye',
                        'source' => [
                            'type' => 'git',
                            'reference' => '1234',
                            'url' => 'foo'
                        ],
                    ]
                ],
            ]), $this->createLock('composer_lock_old', [
                'packages-dev' => [
                    [
                        'name' => 'goodbye',
                        'source' => [
                            'type' => 'git',
                            'reference' => '456',
                            'url' => 'foo'
                        ],
                    ]
                ],
        ]));
        $histories = $compiler->compile();
        $this->assertCount(1, $histories);
        $this->assertTrue($histories->at(0)->hasChanged());
        $this->assertFalse($histories->at(0)->isNew());
    }

    private function createCompiler(string $composerLock, string $composerLockOld): HistoryCompiler
    {
        return new HistoryCompiler($composerLock, $composerLockOld, $this->filter->reveal());
    }

    private function createLock(string $path, array $lock): string
    {
        $path = $this->workspace->path($path);

        file_put_contents($path, json_encode(array_replace_recursive([
            'packages' => [],
        ], $lock)));

        return $path;
    }
}
