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
        ReportOutput $output
    ): void {
        $changed = $this->histories->changed();
        $output->writeln(sprintf(
            '<info>dantleech/what-changed:</> %s changed (comparing last %s lock files)',
            $changed->count(),
            $this->histories->count()
        ));

        if ($changed->count() === 0) {
            return;
        }

        $output->writeln();

        /** @var PackageHistory $history */
        foreach ($changed as $history) {
            if ($history->isNew()) {
                $output->writeln(sprintf('[NEW] <info>%s</>', $history->name()));
                continue;
            }
            $output->writeln(sprintf(
                '[UPD] <info>%s</> %s..%s',
                $history->name(),
                substr($history->first(), 0, 10),
                substr($history->last(), 0, 10)
            ));

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
