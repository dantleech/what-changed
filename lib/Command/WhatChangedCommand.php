<?php

namespace DTL\WhatChanged\Command;

use Composer\Command\BaseCommand;
use DTL\WhatChanged\Adapter\Symfony\ConsoleReportOutput;
use DTL\WhatChanged\Model\ReportOptions;
use DTL\WhatChanged\WhatChangedContainerFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WhatChangedCommand extends BaseCommand
{
    private const OPTION_MERGE_COMMITS = 'merge-commits';
    const OPTION_FULL_MESSAGE = 'full-message';


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
        $this->addOption(self::OPTION_MERGE_COMMITS, null, InputOption::VALUE_NONE, 'Show merge commits');
        $this->addOption(self::OPTION_FULL_MESSAGE, 'F', InputOption::VALUE_NONE, 'Show full commit message');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $options = new ReportOptions();

        $this->configureOptions($options, $input);
        $this->containerFactory->create([])->consoleReport()->render(
            new ConsoleReportOutput($output),
            $options
        );

        return 0;
    }

    private function configureOptions(ReportOptions $options, InputInterface $input)
    {
        $options->showMergeCommits = $input->getOption(self::OPTION_MERGE_COMMITS);

        if ($input->hasOption(self::OPTION_FULL_MESSAGE)) {
            $options->shortMessage = !$input->getOption(self::OPTION_FULL_MESSAGE);
        }
    }
}
