<?php

declare(strict_types=1);

namespace soradore\DDUI\element;

use pocketmine\player\Player;
use soradore\DDUI\properties\FloatProperty;
use soradore\DDUI\properties\ObjectProperty;

class ButtonClickElement extends FloatProperty
{
    public function __construct(?ObjectProperty $parent = null)
    {
        parent::__construct('onClick', 0.0, $parent);
    }

    public function triggerListeners(Player $player, mixed $data): void
    {
        if (is_float($data) || is_int($data)) {
            $newValue = (float) $data;
            if ($newValue <= $this->getValue()) {
                return; // initial sync or stale value, not a real click
            }
            $this->setValue($newValue);
        }
        parent::triggerListeners($player, $data);
    }
}
