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
use DTL\WhatChanged\Command\WhatChangedCommand;
use DTL\WhatChanged\Model\ReportOptions;

class WhatChangedPlugin implements PluginInterface, EventSubscriberInterface, Capable, CommandProvider
{
    public function containerFactory(): WhatChangedContainerFactory
    {
        return new WhatChangedContainerFactory([]);
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
        $this->containerFactory()->create()->archiver()->archive();
    }

    public function handlePostUpdate(Event $event)
    {
        $this->containerFactory()->create()->consoleReport()->render(
            new ComposerReportOutput($event->getIO()),
            new ReportOptions()
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
                $this->containerFactory()
            ),
        ];
    }
}
