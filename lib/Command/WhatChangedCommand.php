<?php

namespace DTL\WhatChanged\Command;

use Composer\Command\BaseCommand;
use DTL\WhatChanged\Adapter\Symfony\ConsoleReportOutput;
use DTL\WhatChanged\Model\PackageHistories;
use DTL\WhatChanged\Model\Report;
use DTL\WhatChanged\WhatChangedContainerFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WhatChangedCommand extends BaseCommand
{
    private const OPTION_LIMIT = 'limit';
    private const OPTION_DIFF = 'diff';

    /**
     * @var WhatChangedContainerFactory
     */
    private $containerFactory;

    public function __construct(
        WhatChangedContainerFactory $containerFactory
    ) {
        parent::__construct();
        $this->containerFactory = $containerFactory;
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
        $this->containerFactory->create([
            'limit' => $input->getOption('limit'),
        ])->consoleReport()->render(new ConsoleReportOutput($output));
    }
}
