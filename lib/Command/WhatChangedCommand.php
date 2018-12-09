<?php

namespace DTL\WhatChanged\Command;

use Composer\Command\BaseCommand;
use DTL\WhatChanged\Model\Change;
use DTL\WhatChanged\Model\ChangelogFactory;
use DTL\WhatChanged\Model\PackageHistories;
use DTL\WhatChanged\Model\PackageHistory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

class WhatChangedCommand extends BaseCommand
{
    /**
     * @var PackageHistories
     */
    private $histories;

    /**
     * @var ChangelogFactory
     */
    private $factory;

    public function __construct(PackageHistories $histories, ChangelogFactory $factory)
    {
        parent::__construct();
        $this->histories = $histories;
        $this->factory = $factory;
    }

    protected function configure()
    {
        $this->setName('what-changed');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var PackageHistory $history */
        foreach ($this->histories->changed() as $history) {
            $output->writeln(sprintf('<info>%s</>', $history->name()));
            $output->writeln(str_repeat('=', strlen($history->name())));
            $output->write(PHP_EOL);
            $output->writeln(sprintf('  %s...%s', $history->first(), $history->last()));
            $output->write(PHP_EOL);

            /** @var Change $change */
            foreach ($this->factory->changeLogFor($history) as $change) {
                $output->writeln(sprintf(
                    '  [<comment>%s</>] %s',
                    $change->date()->format('Y-m-d H:i:s'),
                    $this->formatMessage($change->message())
                ));
            }
            $output->write(PHP_EOL);
        }
    }

    private function formatMessage(string $string): string
    {
        $lines = array_filter(explode(PHP_EOL, $string));
        if (empty($lines)) {
            return '';
        }

        $line = implode(' ', $lines);
        $terminal = new Terminal();

        if (mb_strlen($line) > $terminal->getWidth()) {
            return mb_substr($line, 0, $terminal->getWidth() - 3) . '...';
        }

        return $line;
    }
}
