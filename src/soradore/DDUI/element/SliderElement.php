<?php

declare(strict_types=1);

namespace soradore\DDUI\element;

use pocketmine\player\Player;
use soradore\DDUI\element\options\SliderOptions;
use soradore\DDUI\Observable;
use soradore\DDUI\properties\BoolProperty;
use soradore\DDUI\properties\FloatProperty;
use soradore\DDUI\properties\ObjectProperty;
use soradore\DDUI\properties\StringProperty;

class SliderElement extends Element
{
    public function __construct(
        string $label,
        Observable $value,
        float $min,
        float $max,
        ?SliderOptions $options = null,
        ?ObjectProperty $parent = null,
    ) {
        parent::__construct('slider', $parent);

        $options ??= new SliderOptions();

        $this->setLabel($label);
        $this->setProperty(new FloatProperty('minValue', $min, $this));
        $this->setProperty(new FloatProperty('maxValue', $max, $this));

        if ($options->step instanceof Observable) {
            $stepProp = new FloatProperty('step', (float) $options->step->getValue(), $this);
            $options->step->subscribe(function (float $v) use ($stepProp): ?FloatProperty {
                $stepProp->setValue($v);

                return $stepProp;
            });
            $this->setProperty($stepProp);
        } else {
            $this->setProperty(new FloatProperty('step', (float) $options->step, $this));
        }

        $valueProp = new FloatProperty('value', (float) $value->getValue(), $this);
        $value->subscribe(function (float $v) use ($valueProp): ?FloatProperty {
            $valueProp->setValue($v);

            return $valueProp;
        });
        $valueProp->addListener(function (Player $player, mixed $data) use ($value): void {
            Observable::withOutboundSuppressed(static function () use ($value, $data): void {
                $value->setValue((float) $data);
            });
        });
        $this->setProperty($valueProp);

        $valueProp->addListener(fn(Player $p, mixed $d) => $this->triggerListeners($p, $d));

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

    /** Register an onChange handler. Receives (Player $player, float $newValue). */
    public function onChange(callable $handler): static
    {
        $this->addListener(static fn(Player $p, mixed $d) => $handler($p, (float) $d));

        return $this;
    }

    private function applyDescription(SliderOptions $options): void
    {
        if ($options->description instanceof Observable) {
            $this->setDescriptionObservable($options->description);
        } else {
            $this->setDescription($options->description);
        }
    }

    private function applyDisabled(SliderOptions $options): void
    {
        if ($options->disabled instanceof Observable) {
            $this->setDisabledObservable($options->disabled);
        } else {
            $this->setDisabled($options->disabled);
        }
    }

    private function applyVisible(SliderOptions $options): void
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
        $this->setProperty(new BoolProperty('slider_visible', $visible, $this));

        return $this;
    }

    public function setVisibleObservable(Observable $visible): static
    {
        parent::setVisibleObservable($visible);
        $property = new BoolProperty('slider_visible', $visible->getValue(), $this);
        $visible->subscribe(function (bool $value) use ($property): ?BoolProperty {
            $property->setValue($value);

            return $property;
        });
        $this->setProperty($property);

        return $this;
    }
}
