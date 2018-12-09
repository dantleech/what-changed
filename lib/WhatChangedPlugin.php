<?php

namespace DTL\WhatChanged;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use DTL\WhatChanged\Adapter\Github\GithubChangelogFactory;
use DTL\WhatChanged\Command\WhatChangedCommand;
use DTL\WhatChanged\Model\ChangelogFactory;
use DTL\WhatChanged\Model\ComposerLockArchiver;
use DTL\WhatChanged\Model\HistoryCompiler;
use DTL\WhatChanged\Model\LockFiles;
use DTL\WhatChanged\Model\PackageHistories;

class WhatChangedPlugin implements PluginInterface, EventSubscriberInterface, Capable, CommandProvider
{
    /**
     * @var WhatChangedContainer
     */
    private $container;

    public function __construct()
    {
        $this->container = new WhatChangedContainer();
    }

    /**
     * {@inheritDoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::PRE_UPDATE_CMD => ['handlePreUpdate'],
        ];
    }

    public function handlePreUpdate(Event $event)
    {
        $this->container->archiver()->archive();
    }

    /**
     * {@inheritDoc}
     */
    public function getCapabilities()
    {
        return [
            CommandProvider::class => self::class
        ];
    }

    public function getCommands()
    {
        return [
            new WhatChangedCommand(
                $this->container->histories(),
                $this->container->changelogFactory()
            ),
        ];
    }
}
