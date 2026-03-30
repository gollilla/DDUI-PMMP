<?php

declare(strict_types=1);

namespace soradore\DDUI\element;

use pocketmine\player\Player;
use soradore\DDUI\element\options\ToggleOptions;
use soradore\DDUI\Observable;
use soradore\DDUI\properties\BoolProperty;
use soradore\DDUI\properties\ObjectProperty;
use soradore\DDUI\properties\StringProperty;

class ToggleElement extends Element
{
    public function __construct(
        string $label,
        Observable $value,
        ?ToggleOptions $options = null,
        ?ObjectProperty $parent = null,
    ) {
        parent::__construct('toggle', $parent);

        $options ??= new ToggleOptions();

        $this->setLabel($label);

        $valueProperty = new BoolProperty('toggled', $value->getValue(), $this);
        $value->subscribe(function (bool $v) use ($valueProperty): ?BoolProperty {
            $valueProperty->setValue($v);

            return $valueProperty;
        });
        $valueProperty->addListener(function (Player $player, mixed $data) use ($value): void {
            Observable::withOutboundSuppressed(static function () use ($value, $data): void {
                $value->setValue((bool) $data);
            });
        });
        $this->setProperty($valueProperty);

        $valueProperty->addListener(fn(Player $p, mixed $d) => $this->triggerListeners($p, $d));

        $this->applyDescription($options);
        $this->applyDisabled($options);
        $this->applyVisible($options);
    }

    public function getDescription(): string
    {
        $prop = $this->getProperty('description');

        return $prop instanceof StringProperty ? $prop->getValue() : '';
    }

    public function setDescription(string $description): static
    {
        $this->setProperty(new StringProperty('description', $description, $this));

        return $this;
    }

    public function setDescriptionObservable(Observable $description): static
    {
        $property = new StringProperty('description', $description->getValue(), $this);
        $description->subscribe(function (string $v) use ($property): ?StringProperty {
            $property->setValue($v);

            return $property;
        });
        $this->setProperty($property);

        return $this;
    }

    /** Register an onChange handler. Receives (Player $player, bool $newValue). */
    public function onChange(callable $handler): static
    {
        $this->addListener(static fn(Player $p, mixed $d) => $handler($p, (bool) $d));

        return $this;
    }

    private function applyDescription(ToggleOptions $options): void
    {
        if ($options->description instanceof Observable) {
            $this->setDescriptionObservable($options->description);
        } else {
            $this->setDescription($options->description);
        }
    }

    private function applyDisabled(ToggleOptions $options): void
    {
        if ($options->disabled instanceof Observable) {
            $this->setDisabledObservable($options->disabled);
        } else {
            $this->setDisabled($options->disabled);
        }
    }

    private function applyVisible(ToggleOptions $options): void
    {
        if ($options->visible instanceof Observable) {
            $this->setVisibleObservable($options->visible);
        } else {
            $this->setVisible($options->visible);
        }
    }

    public function setVisible(bool $visible): static
    {
        parent::setVisible($visible);
        $this->setProperty(new BoolProperty('toggle_visible', $visible, $this));

        return $this;
    }

    public function setVisibleObservable(Observable $visible): static
    {
        parent::setVisibleObservable($visible);
        $property = new BoolProperty('toggle_visible', $visible->getValue(), $this);
        $visible->subscribe(function (bool $value) use ($property): ?BoolProperty {
            $property->setValue($value);

            return $property;
        });
        $this->setProperty($property);

        return $this;
    }
}
