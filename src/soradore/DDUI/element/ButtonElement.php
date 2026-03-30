<?php

declare(strict_types=1);

namespace soradore\DDUI\element;

use pocketmine\player\Player;
use soradore\DDUI\element\options\ButtonOptions;
use soradore\DDUI\Observable;
use soradore\DDUI\properties\BoolProperty;
use soradore\DDUI\properties\ObjectProperty;
use soradore\DDUI\properties\StringProperty;

class ButtonElement extends Element
{
    private ButtonClickElement $clickElement;

    public function __construct(string $label, ?ButtonOptions $options = null, ?ObjectProperty $parent = null)
    {
        parent::__construct('button', $parent);

        $options ??= new ButtonOptions();

        $this->setLabel($label);
        $this->applyTooltip($options);
        $this->applyDisabled($options);
        $this->applyVisible($options);

        $this->clickElement = new ButtonClickElement($this);
        $this->setProperty($this->clickElement);
        $this->clickElement->addListener(fn(Player $p, mixed $d) => $this->triggerListeners($p, $d));
    }

    public function getTooltip(): string
    {
        $prop = $this->getProperty('tooltip');

        return $prop instanceof StringProperty ? $prop->getValue() : '';
    }

    public function setTooltip(string $tooltip): static
    {
        $this->setProperty(new StringProperty('tooltip', $tooltip, $this));

        return $this;
    }

    public function setTooltipObservable(Observable $tooltip): static
    {
        $property = new StringProperty('tooltip', $tooltip->getValue(), $this);
        $tooltip->subscribe(function (string $value) use ($property): ?StringProperty {
            $property->setValue($value);

            return $property;
        });
        $this->setProperty($property);

        return $this;
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

    private function applyTooltip(ButtonOptions $options): void
    {
        if ($options->tooltip instanceof Observable) {
            $this->setTooltipObservable($options->tooltip);
        } else {
            $this->setTooltip($options->tooltip);
        }
    }

    private function applyDisabled(ButtonOptions $options): void
    {
        if ($options->disabled instanceof Observable) {
            $this->setDisabledObservable($options->disabled);
        } else {
            $this->setDisabled($options->disabled);
        }
    }

    private function applyVisible(ButtonOptions $options): void
    {
        if ($options->visible instanceof Observable) {
            $this->setVisibleObservable($options->visible);
        } else {
            $this->setVisible($options->visible);
        }
    }
}
