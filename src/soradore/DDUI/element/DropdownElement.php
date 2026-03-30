<?php

declare(strict_types=1);

namespace soradore\DDUI\element;

use pocketmine\player\Player;
use soradore\DDUI\element\options\DropdownOptions;
use soradore\DDUI\Observable;
use soradore\DDUI\properties\FloatProperty;
use soradore\DDUI\properties\ObjectProperty;
use soradore\DDUI\properties\StringListProperty;
use soradore\DDUI\properties\StringProperty;

class DropdownElement extends Element
{
    /**
     * @param string[]   $items    Display labels for each option.
     * @param Observable $selected Observable holding the selected index (0-based).
     */
    public function __construct(
        string $label,
        array $items,
        Observable $selected,
        ?DropdownOptions $options = null,
        ?ObjectProperty $parent = null,
    ) {
        parent::__construct('dropdown', $parent);

        $options ??= new DropdownOptions();

        $this->setLabel($label);
        $this->setProperty(new StringListProperty('items', $items, $this));

        $selectedProp = new FloatProperty('selectedItem', (float) $selected->getValue(), $this);
        $selected->subscribe(function (int|float $v) use ($selectedProp): ?FloatProperty {
            $selectedProp->setValue((float) $v);

            return $selectedProp;
        });
        $selectedProp->addListener(function (Player $player, mixed $data) use ($selected): void {
            Observable::withOutboundSuppressed(static function () use ($selected, $data): void {
                $selected->setValue((int) (float) $data);
            });
        });
        $this->setProperty($selectedProp);

        $selectedProp->addListener(fn(Player $p, mixed $d) => $this->triggerListeners($p, $d));

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

    /** Register an onChange handler. Receives (Player $player, int $selectedIndex). */
    public function onChange(callable $handler): static
    {
        $this->addListener(static fn(Player $p, mixed $d) => $handler($p, (int) (float) $d));

        return $this;
    }

    private function applyDescription(DropdownOptions $options): void
    {
        if ($options->description instanceof Observable) {
            $this->setDescriptionObservable($options->description);
        } else {
            $this->setDescription($options->description);
        }
    }

    private function applyDisabled(DropdownOptions $options): void
    {
        if ($options->disabled instanceof Observable) {
            $this->setDisabledObservable($options->disabled);
        } else {
            $this->setDisabled($options->disabled);
        }
    }

    private function applyVisible(DropdownOptions $options): void
    {
        if ($options->visible instanceof Observable) {
            $this->setVisibleObservable($options->visible);
        } else {
            $this->setVisible($options->visible);
        }
    }
}
