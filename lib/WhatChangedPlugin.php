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
use DTL\WhatChanged\Adapter\Composer\ComposerReportOutput;
use DTL\WhatChanged\Adapter\Symfony\ConsoleReportOutput;
use DTL\WhatChanged\Command\WhatChangedCommand;

class WhatChangedPlugin implements PluginInterface, EventSubscriberInterface, Capable, CommandProvider
{
    /**
     * @var WhatChangedContainer
     */
    private $container;

    public function __construct()
    {
        $this->container = new WhatChangedContainer(getcwd());
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
            ScriptEvents::POST_UPDATE_CMD => ['handlePostUpdate'],
        ];
    }

    public function handlePreUpdate(Event $event)
    {
        $this->container->archiver()->archive();
    }

    public function handlePostUpdate(Event $event)
    {
        $histories = $this->container->histories()->tail(2);
        $this->container->consoleReport()->render(
            new ComposerReportOutput($event->getIO()),
            $histories
        );
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
                $this->container->consoleReport()
            ),
        ];
    }
}
