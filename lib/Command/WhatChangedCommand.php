<?php

namespace DTL\WhatChanged\Command;

use Composer\Command\BaseCommand;
use DTL\WhatChanged\Adapter\Symfony\ConsoleReportOutput;
use DTL\WhatChanged\Model\Change;
use DTL\WhatChanged\Model\ChangelogFactory;
use DTL\WhatChanged\Model\PackageHistories;
use DTL\WhatChanged\Model\PackageHistory;
use DTL\WhatChanged\Model\Report;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

class WhatChangedCommand extends BaseCommand
{
    const OPTION_LIMIT = 'limit';

    /**
     * @var PackageHistories
     */
    private $histories;

    /**
     * @var Report
     */
    private $report;

    public function __construct(
        PackageHistories $histories,
        Report $report
    )
    {
        parent::__construct();
        $this->histories = $histories;
        $this->report = $report;
    }

    protected function configure()
    {
        $this->setName('what-changed');
        $this->setDescription('Show what changed since your last update');
        $this->addOption(self::OPTION_LIMIT, null, InputOption::VALUE_REQUIRED, 'Number of composer lock files to compare', 2);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $histories = $this->histories->tail((int) $input->getOption('limit'));
        $output->writeln($this->report->render(
            new ConsoleReportOutput($output),
            $histories
        ));
    }
}
