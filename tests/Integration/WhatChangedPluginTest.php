<?php

namespace DTL\WhatChanged\Tests\Integration;

use Composer\Composer;
use Composer\Config;
use Composer\IO\BufferIO;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use DTL\WhatChanged\WhatChangedPlugin;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class WhatChangedPluginTest extends TestCase
{
    use ProphecyTrait;
    /**
     * @var WhatChangedPlugin
     */
    private $plugin;

    /**
     * @var ObjectProphecy
     */
    private $scriptEvent;

    /**
     * @var BufferIO
     */
    private $io;

    public function setUp(): void
    {
        $this->plugin = new WhatChangedPlugin();
        $this->scriptEvent = $this->prophesize(Event::class);
        $this->config = new Config();
        $this->composer = $this->prophesize(Composer::class);
        $this->scriptEvent->getComposer()->willReturn($this->composer);
        $this->composer->getConfig()->willReturn($this->config);
        $this->io = new BufferIO();
        $this->scriptEvent->getIO()->willReturn($this->io);
    }

    public function testCapabilities()
    {
        $capabilities = $this->plugin->getCapabilities();

        $this->assertEquals([
            CommandProvider::class => WhatChangedPlugin::class
        ], $capabilities);
    }

    public function testSubscribedEvents()
    {
        $expected = [
            ScriptEvents::PRE_UPDATE_CMD => ['handlePreUpdate'],
            ScriptEvents::POST_UPDATE_CMD => ['handlePostUpdate'],
        ];

        $this->assertEquals($expected, $this->plugin->getSubscribedEvents());
    }

    public function testPreUpdate()
    {
        $this->plugin->handlePreUpdate($this->scriptEvent->reveal());
        $this->addToAssertionCount(1);
    }

    public function testPostUpdate()
    {
        $this->plugin->handlePostUpdate($this->scriptEvent->reveal());
        $this->addToAssertionCount(1);
    }
}
