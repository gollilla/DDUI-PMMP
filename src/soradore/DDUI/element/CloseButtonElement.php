<?php

declare(strict_types=1);

namespace soradore\DDUI\element;

use pocketmine\player\Player;
use soradore\DDUI\element\options\CloseButtonOptions;
use soradore\DDUI\Observable;
use soradore\DDUI\properties\BoolProperty;
use soradore\DDUI\properties\ObjectProperty;

class CloseButtonElement extends Element
{
    public function __construct(?CloseButtonOptions $options = null, ?ObjectProperty $parent = null)
    {
        parent::__construct('closeButton', $parent);

        $options ??= new CloseButtonOptions();

        if ($options->label instanceof Observable) {
            $this->setLabelObservable($options->label);
        } else {
            $this->setLabel($options->label);
        }

        if ($options->visible instanceof Observable) {
            $this->setVisibleObservable($options->visible);
        } else {
            $this->setVisible($options->visible);
        }

        $clickElement = new ButtonClickElement($this);
        $this->setProperty($clickElement);
        $clickElement->addListener(fn(Player $p, mixed $d) => $this->triggerListeners($p, $d));
    }

    public function setVisible(bool $visible): static
    {
        parent::setVisible($visible);
        $this->setProperty(new BoolProperty('button_visible', $visible, $this));

        return $this;
    }

    public function setVisibleObservable(Observable $visible): static
    {
        parent::setVisibleObservable($visible);
        $property = new BoolProperty('button_visible', $visible->getValue(), $this);
        $visible->subscribe(function (bool $value) use ($property): ?BoolProperty {
            $property->setValue($value);

            return $property;
        });
        $this->setProperty($property);

        return $this;
    }

    /** Register a click handler. Receives (Player $player). */
    public function onClick(callable $handler): static
    {
        $this->addListener(static fn(Player $p, mixed $_) => $handler($p));

        return $this;
    }
}
