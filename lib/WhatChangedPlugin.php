<?php

namespace DTL\WhatChanged;

use Composer\Composer;
use Composer\Config;
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
    const COMPOSER_GITHUB_TOKEN = 'github-oauth';

    /**
     * @var Composer
     */
    private $composer;

    /**
     * {@inheritDoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
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
            $event->getIO()->writeError('dantleech/whatchanged: ERROR: ' . $e->getMessage());
        }
    }

    public function handlePostUpdate(Event $event)
    {
        try {
            $this->containerFactory([
               WhatChangedContainerFactory::KEY_GITHUB_OAUTH  => $this->resolveGithubToken($event->getComposer()->getConfig())
            ])->create()->consoleReport()->render(
                new ComposerReportOutput($event->getIO()),
                new ReportOptions()
            );
        } catch (WhatChangedRuntimeException $e) {
            $event->getIO()->writeError('dantleech/whatchanged: ERROR: ' . $e->getMessage());
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

    private function containerFactory(array $config = []): WhatChangedContainerFactory
    {
        return new WhatChangedContainerFactory($config);
    }

    /**
     * {@inheritDoc}
     */
    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function uninstall(Composer$composer, IOInterface $io)
    {
    }

    private function resolveGithubToken(Config $config): ?string
    {
        if (!$config->has(self::COMPOSER_GITHUB_TOKEN)) {
            return null;
        }

        return $this->composer->getConfig()->get(self::COMPOSER_GITHUB_TOKEN)['github.com'] ?? null;
    }
}
