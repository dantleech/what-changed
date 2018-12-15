<?php

namespace DTL\WhatChanged\Tests\Unit\Report;

use ArrayIterator;
use DTL\WhatChanged\Model\ChangeBuilder;
use DTL\WhatChanged\Model\Changelog;
use DTL\WhatChanged\Model\ChangelogFactory;
use DTL\WhatChanged\Model\Output\BufferedReportOutput;
use DTL\WhatChanged\Model\PackageHistories;
use DTL\WhatChanged\Model\PackageHistory;
use DTL\WhatChanged\Model\Report;
use DTL\WhatChanged\Model\ReportOptions;
use DTL\WhatChanged\Adapter\Symfony\Report\ConsoleReport;
use PHPUnit\Framework\TestCase;

class ConsoleReportTest extends TestCase
{
    /**
     * @var ConsoleReport
     */
    private $report;

    /**
     * @var BufferedReportOutput
     */
    private $output;

    /**
     * @var ObjectProphecy
     */
    private $factory;

    public function setUp()
    {
        $this->factory = $this->prophesize(ChangelogFactory::class);
        $this->output = new BufferedReportOutput();
    }

    public function testRendersChangedPackages()
    {
        $packageHistory = new PackageHistory('one', 'two', 'three');
        $packageHistory->addReference('asd');
        $packageHistory->addReference('dsa');

        $report = $this->create([ $packageHistory ]);
        $this->factory->changeLogFor($packageHistory)->willReturn($this->createChangelog([
            $this->changeBuilder()->rawDate('2018-01-01')->message('Hello World')->build(),
            $this->changeBuilder()->rawDate('2018-01-01')->message('Goodbye World')->build(),
        ]));
        $options = new ReportOptions();
        $report->render($this->output, $options);

        $this->assertContains('Hello World', (string) $this->output);
        $this->assertContains('Goodbye World', (string) $this->output);
    }

    public function testIgnoresMergeCommits()
    {
        $packageHistory = new PackageHistory('one', 'two', 'three');
        $packageHistory->addReference('asd');
        $packageHistory->addReference('dsa');

        $report = $this->create([ $packageHistory ]);
        $this->factory->changeLogFor($packageHistory)->willReturn($this->createChangelog([
            $this->changeBuilder()->rawDate('2018-01-01')->message('Hello World')->parents(['one', 'two'])->build(),
            $this->changeBuilder()->rawDate('2018-01-01')->message('Goodbye World')->build(),
        ]));

        $options = new ReportOptions();
        $report->render($this->output, $options);

        $this->assertNotContains('Hello World', (string) $this->output);
        $this->assertContains('Goodbye World', (string) $this->output);
    }

    public function testShowsMergeCommits()
    {
        $packageHistory = new PackageHistory('one', 'two', 'three');
        $packageHistory->addReference('asd');
        $packageHistory->addReference('dsa');

        $report = $this->create([ $packageHistory ]);
        $this->factory->changeLogFor($packageHistory)->willReturn($this->createChangelog([
            $this->changeBuilder()->rawDate('2018-01-01')->message('Hello World')->parents(['one', 'two'])->build(),
            $this->changeBuilder()->rawDate('2018-01-01')->message('Goodbye World')->build(),
        ]));

        $options = new ReportOptions();
        $options->showMergeCommits = true;
        $report->render($this->output, $options);

        $this->assertContains('Hello World', (string) $this->output);
        $this->assertContains('Goodbye World', (string) $this->output);
    }

    private function create(array $histories): Report
    {
        $histories = new PackageHistories($histories);
        return new ConsoleReport($histories, $this->factory->reveal());
    }

    private function changeBuilder(): ChangeBuilder
    {
        return ChangeBuilder::create()
            ->author('dantleech')
            ->message('hello')
            ->rawDate('2018-01-01')
            ->parents([])
            ->sha('abcd1234');
    }

    private function createChangelog(array $array)
    {
        return new class($array) implements Changelog {
            private $changes;
            public function __construct(array $changes)
            {
                $this->changes = $changes;
            }
            public function getIterator()
            {
                return new ArrayIterator($this->changes);
            }
        };
    }
}
