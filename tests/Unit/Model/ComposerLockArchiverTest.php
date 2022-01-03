<?php

namespace DTL\WhatChanged\Tests\Unit\Model;

use DTL\WhatChanged\Exception\WhatChangedRuntimeException;
use DTL\WhatChanged\Model\ComposerLockArchiver;
use DTL\WhatChanged\Model\Exception\CouldNotArchiveComposerLock;
use DTL\WhatChanged\Model\Filesystem;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ComposerLockArchiverTest extends TestCase
{
    use ProphecyTrait;
    const EXAMPLE_LOCK = 'lock_path.lock';
    const EXAMPLE_COMPARE = 'compare_path.lock';


    /**
     * @var ObjectProphecy|Filesystem
     */
    private $filesystem;

    /**
     * @var ComposerLockArchiver
     */
    private $archiver;

    public function setUp(): void
    {
        $this->filesystem = $this->prophesize(Filesystem::class);
        $this->archiver = new ComposerLockArchiver(
            $this->filesystem->reveal(),
            self::EXAMPLE_LOCK,
            self::EXAMPLE_COMPARE
        );
    }

    public function testReturnsIfNoLockFileExists()
    {
        $this->filesystem->exists(self::EXAMPLE_LOCK)
            ->willReturn(false)
            ->shouldBeCalled();

        $this->archiver->archive();
    }

    public function testCopiesLockFile()
    {
        $this->filesystem->exists(self::EXAMPLE_LOCK)
            ->willReturn(true);

        $this->filesystem->copy(self::EXAMPLE_LOCK, self::EXAMPLE_COMPARE)
            ->shouldBeCalled();

        $this->archiver->archive();
    }

    public function testThrowsExceptionIfFileCouldNotBeCopied()
    {
        $this->expectException(CouldNotArchiveComposerLock::class);

        $this->filesystem->exists(self::EXAMPLE_LOCK)
            ->willReturn(true);

        $this->filesystem->copy(self::EXAMPLE_LOCK, self::EXAMPLE_COMPARE)
            ->willThrow(new WhatChangedRuntimeException('No'));

        $this->archiver->archive();
    }
}
