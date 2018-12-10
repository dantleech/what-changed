<?php

namespace DTL\WhatChanged\Report;

use DTL\WhatChanged\Model\Change;
use DTL\WhatChanged\Model\ChangelogFactory;
use DTL\WhatChanged\Model\PackageHistories;
use DTL\WhatChanged\Model\PackageHistory;
use DTL\WhatChanged\Model\Report;
use DTL\WhatChanged\Model\ReportOptions;
use DTL\WhatChanged\Model\ReportOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

class ConsoleReport implements Report
{
    /**
     * @var ChangelogFactory
     */
    private $factory;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var PackageHistories
     */
    private $histories;

    public function __construct(
        PackageHistories $histories,
        ChangelogFactory $factory
    ) {
        $this->factory = $factory;
        $this->histories = $histories;
    }

    public function render(
        ReportOutput $output,
        ReportOptions $options
    ): void {
        $changed = $this->histories->changed();
        $this->whatRemoved($output, $changed, $options);
        $this->whatNew($output, $changed, $options);
        $this->whatUpdated($output, $changed, $options);
    }

    private function formatMessage(string $string): string
    {
        $line = str_replace(["\n", "\r\n", "\r"], ' ', $string);

        $width = $this->terminalWidth();

        if (mb_strlen($line) > $width) {
            return mb_substr($line, 0, $width - 3) . '...';
        }

        return $line;
    }

    private function whatNew(ReportOutput $output, PackageHistories $changed, ReportOptions $options)
    {
        if ($changed->new()->count() === 0) {
            return;
        }

        $output->writeln(sprintf(
            '<info>dantleech/what-changed:</> %s new',
            $changed->new()->count()
        ));

        /** @var PackageHistory $history */
        foreach ($changed->new() as $history) {
            $output->writeln(sprintf(
                '  - %s',
                $history->name()
            ));
        }
        $output->writeln();
    }

    private function whatRemoved(ReportOutput $output, PackageHistories $changed, ReportOptions $options)
    {
        if ($changed->removed()->count() === 0) {
            return;
        }

        $output->writeln(sprintf(
            '<info>dantleech/what-changed:</> %s removed',
            $changed->removed()->count()
        ));

        /** @var PackageHistory $history */
        foreach ($changed->removed() as $history) {
            $output->writeln(sprintf(
                '  - %s',
                $history->name()
            ));
        }
        $output->writeln();
    }

    private function whatUpdated(ReportOutput $output, PackageHistories $changed, ReportOptions $options)
    {
        if ($changed->updated()->count() === 0) {
            return;
        }
        
        $output->writeln(sprintf(
            '<info>dantleech/what-changed:</> %s updated',
            $changed->updated()->count()
        ));
        
        $output->writeln();
        
        /** @var PackageHistory $history */
        foreach ($changed->updated() as $history) {
            $output->writeln(sprintf(
                '  <info>%s</> %s..%s',
                $history->name(),
                substr($history->first(), 0, 10),
                substr($history->last(), 0, 10)
            ));
        
            $changelog = $this->factory->changeLogFor($history);
        
            $index = 0;
            /** @var Change $change */
            foreach ($changelog as $index => $change) {
                if (false === $options->showMergeCommits && $change->isMerge()) {
                    continue;
                }
        
                if ($index++ === 0) {
                    $output->writeln();
                }
        
                $output->writeln(sprintf(
                    '    [<comment>%s</>] %s',
                    $change->date()->format('Y-m-d H:i:s'),
                    $this->formatMessage($change->message())
                ));
            }
            $output->writeln();
        }
    }

    private function terminalWidth(): int
    {
        if (class_exists(Terminal::class)) {
            $terminal = new Terminal();
            return $terminal->getWidth() - 30;
        }

        return 80;
    }
}
