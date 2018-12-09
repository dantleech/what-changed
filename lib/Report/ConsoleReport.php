<?php

namespace DTL\WhatChanged\Report;

use DTL\WhatChanged\Model\Change;
use DTL\WhatChanged\Model\ChangelogFactory;
use DTL\WhatChanged\Model\PackageHistories;
use DTL\WhatChanged\Model\PackageHistory;
use DTL\WhatChanged\Model\Report;
use DTL\WhatChanged\Model\ReportOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

class ConsoleReport implements Report
{
    /**
     * @var PackageHistories
     */
    private $histories;

    /**
     * @var ChangelogFactory
     */
    private $factory;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(
        PackageHistories $histories,
        ChangelogFactory $factory
    ) {
        $this->histories = $histories;
        $this->factory = $factory;
    }

    public function render(
        ReportOutput $output,
        PackageHistories $histories
    ): void {
        $changed = $histories->changed();
        $output->writeln(sprintf(
            'Showing changes from %s lock files, %s changed',
            $histories->count(),
            $changed->count()
        ));
        $output->writeln();

        /** @var PackageHistory $history */
        foreach ($changed as $history) {
            $output->writeln(sprintf('<info>%s</>', $history->name()));
            $output->writeln();
            $output->writeln(sprintf('  %s...%s', $history->first(), $history->last()));
            $output->writeln();

            /** @var Change $change */
            foreach ($this->factory->changeLogFor(
                $history
            ) as $change) {
                $output->writeln(sprintf(
                    '  [<comment>%s</>] %s',
                    $change->date()->format('Y-m-d H:i:s'),
                    $this->formatMessage($change->message())
                ));
            }
            $output->writeln();
        }
    }

    private function formatMessage(string $string): string
    {
        $line = str_replace(["\n", "\r\n", "\r"], ' ', $string);

        $terminal = new Terminal();
        $width = $terminal->getWidth() - 30;

        if (mb_strlen($line) > $width) {
            return mb_substr($line, 0, $width - 3) . '...';
        }

        return $line;
    }
}
