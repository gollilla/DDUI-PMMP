<?php

declare(strict_types=1);

namespace soradore\DDUI;

use pocketmine\plugin\PluginBase;
use soradore\DDUI\Listener\DduiListener;

class Main extends PluginBase
{
    private static self $instance;

    protected function onEnable(): void
    {
        self::$instance = $this;
        $this->getServer()->getPluginManager()->registerEvents(new DduiListener(), $this);
    }

    public static function getInstance(): self
    {
        return self::$instance;
    }
}
