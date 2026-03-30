<?php

declare(strict_types=1);

namespace soradore\DDUI\element;

use pocketmine\player\Player;
use soradore\DDUI\element\options\TextFieldOptions;
use soradore\DDUI\Observable;
use soradore\DDUI\properties\BoolProperty;
use soradore\DDUI\properties\ObjectProperty;
use soradore\DDUI\properties\StringProperty;

class TextFieldElement extends Element
{
    public function __construct(
        string $label,
        Observable $text,
        ?TextFieldOptions $options = null,
        ?ObjectProperty $parent = null,
    ) {
        parent::__construct('textField', $parent);

        $options ??= new TextFieldOptions();

        $this->setLabel($label);

        $textProperty = new StringProperty('text', $text->getValue(), $this);
        $text->subscribe(function (string $v) use ($textProperty): ?StringProperty {
            $textProperty->setValue($v);

            return $textProperty;
        });
        $textProperty->addListener(function (Player $player, mixed $data) use ($text): void {
            Observable::withOutboundSuppressed(static function () use ($text, $data): void {
                $text->setValue((string) $data);
            });
        });
        $this->setProperty($textProperty);

        $textProperty->addListener(fn(Player $p, mixed $d) => $this->triggerListeners($p, $d));

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

    /** Register an onChange handler. Receives (Player $player, string $newValue). */
    public function onChange(callable $handler): static
    {
        $this->addListener(static fn(Player $p, mixed $d) => $handler($p, (string) $d));

        return $this;
    }

    private function applyDescription(TextFieldOptions $options): void
    {
        if ($options->description instanceof Observable) {
            $this->setDescriptionObservable($options->description);
        } else {
            $this->setDescription($options->description);
        }
    }

    private function applyDisabled(TextFieldOptions $options): void
    {
        if ($options->disabled instanceof Observable) {
            $this->setDisabledObservable($options->disabled);
        } else {
            $this->setDisabled($options->disabled);
        }
    }

    private function applyVisible(TextFieldOptions $options): void
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
        $this->setProperty(new BoolProperty('textfield_visible', $visible, $this));

        return $this;
    }

    public function setVisibleObservable(Observable $visible): static
    {
        parent::setVisibleObservable($visible);
        $property = new BoolProperty('textfield_visible', $visible->getValue(), $this);
        $visible->subscribe(function (bool $value) use ($property): ?BoolProperty {
            $property->setValue($value);

            return $property;
        });
        $this->setProperty($property);

        return $this;
    }
}
