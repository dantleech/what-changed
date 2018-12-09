<?php

namespace DTL\WhatChanged\Command;

use Composer\Command\BaseCommand;
use DTL\WhatChanged\Adapter\Symfony\ConsoleReportOutput;
use DTL\WhatChanged\Model\PackageHistories;
use DTL\WhatChanged\Model\Report;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WhatChangedCommand extends BaseCommand
{
    private const OPTION_LIMIT = 'limit';
    private const OPTION_DIFF = 'diff';

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
    ) {
        parent::__construct();
        $this->histories = $histories;
        $this->report = $report;
    }

    protected function configure()
    {
        $this->setName('what-changed');
        $this->setDescription('Show what changed since your last update');
        $this->addOption(self::OPTION_LIMIT, null, InputOption::VALUE_REQUIRED, 'Number of composer lock files to compare', 2);
        $this->addOption(self::OPTION_DIFF, null, InputOption::VALUE_NONE, 'Show git diff for each changed package');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $limit = $input->getOption('limit');
        $histories = $this->histories->tail(
            is_numeric($limit) ? (int) $limit : PHP_INT_MAX
        );

        $this->report->render(
            new ConsoleReportOutput($output),
            $histories
        );
    }
}
