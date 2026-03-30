<?php

declare(strict_types=1);

namespace soradore\DDUI\element;

use pocketmine\player\Player;
use soradore\DDUI\Observable;
use soradore\DDUI\properties\BoolProperty;
use soradore\DDUI\properties\ObjectProperty;
use soradore\DDUI\properties\StringProperty;

abstract class Element extends ObjectProperty
{
    /** @var callable[] (Player, mixed): void */
    private array $elementListeners = [];

    public function __construct(string $typeName, ?ObjectProperty $parent = null)
    {
        parent::__construct($typeName, $parent);
    }

    public function getLabel(): string
    {
        $prop = $this->getProperty('label');

        return $prop instanceof StringProperty ? $prop->getValue() : '';
    }

    public function setLabel(string $label): static
    {
        $this->setProperty(new StringProperty('label', $label, $this));

        return $this;
    }

    public function setLabelObservable(Observable $label): static
    {
        $property = new StringProperty('label', $label->getValue(), $this);
        $label->subscribe(function (string $value) use ($property): ?StringProperty {
            $property->setValue($value);

            return $property;
        });
        $this->setProperty($property);

        return $this;
    }

    public function isDisabled(): bool
    {
        $prop = $this->getProperty('disabled');

        return $prop instanceof BoolProperty ? $prop->getValue() : false;
    }

    public function setDisabled(bool $disabled): static
    {
        $this->setProperty(new BoolProperty('disabled', $disabled, $this));

        return $this;
    }

    public function setDisabledObservable(Observable $disabled): static
    {
        $property = new BoolProperty('disabled', $disabled->getValue(), $this);
        $disabled->subscribe(function (bool $value) use ($property): ?BoolProperty {
            $property->setValue($value);

            return $property;
        });
        $this->setProperty($property);

        return $this;
    }

    public function isVisible(): bool
    {
        $prop = $this->getProperty('visible');

        return $prop instanceof BoolProperty ? $prop->getValue() : true;
    }

    public function setVisible(bool $visible): static
    {
        $this->setProperty(new BoolProperty('visible', $visible, $this));

        return $this;
    }

    public function setVisibleObservable(Observable $visible): static
    {
        $property = new BoolProperty('visible', $visible->getValue(), $this);
        $visible->subscribe(function (bool $value) use ($property): ?BoolProperty {
            $property->setValue($value);

            return $property;
        });
        $this->setProperty($property);

        return $this;
    }

    public function addListener(callable $listener): static
    {
        $this->elementListeners[] = $listener;

        return $this;
    }

    public function triggerListeners(Player $player, mixed $data): void
    {
        foreach ($this->elementListeners as $listener) {
            ($listener)($player, $data);
        }
    }
}
