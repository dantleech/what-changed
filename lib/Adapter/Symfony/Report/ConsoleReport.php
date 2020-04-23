<?php

namespace DTL\WhatChanged\Adapter\Symfony\Report;

use DTL\WhatChanged\Model\Change;
use DTL\WhatChanged\Model\ChangelogFactory;
use DTL\WhatChanged\Model\PackageHistories;
use DTL\WhatChanged\Model\PackageHistory;
use DTL\WhatChanged\Model\Report;
use DTL\WhatChanged\Model\ReportOptions;
use DTL\WhatChanged\Model\ReportOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\FormatterHelper;
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

    /**
     * @var OutputFormatter
     */
    private $formatter;

    public function __construct(
        PackageHistories $histories,
        ChangelogFactory $factory
    ) {
        $this->factory = $factory;
        $this->histories = $histories;
        $this->formatter = new OutputFormatter();
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

    private function whatNew(ReportOutput $output, PackageHistories $changed, ReportOptions $options)
    {
        if ($changed->new()->count() === 0) {
            $output->writeln(
                '<info>dantleech/what-changed:</> nothing changed'
            );
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
                substr($history->first(), 0, 8),
                substr($history->last(), 0, 8)
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

                $message = $this->formatChange($change, $options);
                $output->writeln($message);

                if (false === $options->shortMessage) {
                    $output->writeln();
                    $output->writeln($this->indent($change->message(), 6));
                    $output->writeln();
                }
            }

            if ($options->shortMessage) {
                $output->writeln();
            }
        }
    }

    private function terminalWidth(): int
    {
        if (class_exists(Terminal::class)) {
            $terminal = new Terminal();
            return $terminal->getWidth();
        }

        return 80;
    }

    private function formatChange(Change $change, ReportOptions $options)
    {
        $parts = [];
        $parts[] = '   ';

        if ($options->showCommitDates) {
            $date = $change->date()->format('Y-m-d H:i:s');
            $parts[] = sprintf('[<comment>%s</>]', $date);
        }

        if ($options->showCommitSha) {
            $parts[] = sprintf('<info>%s</>', substr($change->sha(), 0, 8));
        }

        if ($options->showAuthor) {
            $parts[] = sprintf('<comment>%s</comment>', $change->author());
        }

        if ($options->shortMessage) {
            $message = $this->sanitizeOneLineMessage($change->message());
            $parts[] = $message;
        }

        return $this->truncateToTerminalWidth(implode(' ', $parts));
    }

    private function indent(string $string, int $int)
    {
        $line = str_replace(["\n", "\r\n", "\r"], PHP_EOL, $string);
        $lines = explode(PHP_EOL, $line);
        return implode(PHP_EOL, array_map(function (string $line) use ($int) {
            return str_repeat(' ', $int) . $line;
        }, $lines));
    }

    private function sanitizeOneLineMessage(string $string): string
    {
        return str_replace(["\n", "\r\n", "\r"], ' ', $string);
    }

    private function truncateToTerminalWidth(string $line)
    {
        $width = $this->terminalWidth();
        $realLength = FormatterHelper::strlenWithoutDecoration($this->formatter, $line);

        if ($realLength > $width) {
            return mb_substr(
                $line,
                0,
                (int) (mb_strlen($line) - ($realLength - $width) - 3)
            ) . '...';
        }

        return $line;
    }
}
