<?php

namespace DTL\WhatChanged\Tests\Unit\Model;

use DTL\WhatChanged\Model\Filesystem;
use DTL\WhatChanged\Model\Filter;
use DTL\WhatChanged\Model\HistoryCompiler;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class HistoryCompilerTest extends TestCase
{
    use ProphecyTrait;
    /**
     * @var ObjectProphecy&Filter
     */
    private $filter;

    /**
     * @var Filesystem&ObjectProphecy
     */
    private $filesystem;

    public function setUp(): void
    {
        $this->filesystem = $this->prophesize(Filesystem::class);
        $this->filter = $this->prophesize(Filter::class);
        $this->filter->isValid(Argument::any())->willReturn(true);
    }

    public function testNoFilesExisting()
    {
        $noExistPath1 = 'noexist1';
        $noExistPath2 = 'noexist2';
        $this->filesystem->exists($noExistPath1)->willReturn(false);
        $this->filesystem->exists($noExistPath2)->willReturn(false);
        $compiler = $this->createCompiler(
            $noExistPath1,
            $noExistPath2
        );
        $histories = $compiler->compile();
        $this->assertCount(0, $histories);
    }

    public function testNoChangeWithOneLock()
    {
        $noExist = 'noexist2';
        $this->filesystem->exists($noExist)->willReturn(false);
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
        ]), $noExist);
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
        return new HistoryCompiler($this->filesystem->reveal(), $composerLock, $composerLockOld, $this->filter->reveal());
    }

    private function createLock(string $path, array $lock): string
    {
        $this->filesystem->exists($path)->willReturn(true);
        $this->filesystem->getContents($path)->willReturn(json_encode(array_replace_recursive([
            'packages' => [],
        ], $lock)));

        return $path;
    }
}
