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
use DTL\WhatChanged\Exception\WhatChangedRuntimeException;
use DTL\WhatChanged\Model\ReportOptions;

class WhatChangedPlugin implements PluginInterface, EventSubscriberInterface, Capable, CommandProvider
{
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

    public function handlePreUpdate(Event $event): void
    {
        try {
            $this->containerFactory()->create()->archiver()->archive();
        } catch (WhatChangedRuntimeException $e) {
            $event->getIO()->writeError($e->getMessage());
        }
    }

    public function handlePostUpdate(Event $event)
    {
        try {
            $this->containerFactory()->create()->consoleReport()->render(
                new ComposerReportOutput($event->getIO()),
                new ReportOptions()
            );
        } catch (WhatChangedRuntimeException $e) {
            $event->getIO()->writeError($e->getMessage());
        }
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

    private function containerFactory(): WhatChangedContainerFactory
    {
        return new WhatChangedContainerFactory([]);
    }
}
