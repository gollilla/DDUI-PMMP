<?php

declare(strict_types=1);

namespace soradore\DDUI\element;

use soradore\DDUI\element\options\LabelOptions;
use soradore\DDUI\Observable;
use soradore\DDUI\properties\BoolProperty;
use soradore\DDUI\properties\ObjectProperty;
use soradore\DDUI\properties\StringProperty;

class LabelElement extends Element
{
    public function __construct(
        string $text,
        ?LabelOptions $options = null,
        ?ObjectProperty $parent = null,
    ) {
        parent::__construct('label', $parent);

        $options ??= new LabelOptions();

        $this->setText($text);
        $this->applyVisible($options);
    }

    public function getText(): string
    {
        $prop = $this->getProperty('text');

        return $prop instanceof StringProperty ? $prop->getValue() : '';
    }

    public function setText(string $text): static
    {
        $this->setProperty(new StringProperty('text', $text, $this));

        return $this;
    }

    public function setTextObservable(Observable $text): static
    {
        $property = new StringProperty('text', $text->getValue(), $this);
        $text->subscribe(function (string $v) use ($property): ?StringProperty {
            $property->setValue($v);

            return $property;
        });
        $this->setProperty($property);

        return $this;
    }

    public function setVisible(bool $visible): static
    {
        parent::setVisible($visible);
        $this->setProperty(new BoolProperty('label_visible', $visible, $this));

        return $this;
    }

    public function setVisibleObservable(Observable $visible): static
    {
        parent::setVisibleObservable($visible);
        $property = new BoolProperty('label_visible', $visible->getValue(), $this);
        $visible->subscribe(function (bool $v) use ($property): ?BoolProperty {
            $property->setValue($v);

            return $property;
        });
        $this->setProperty($property);

        return $this;
    }

    private function applyVisible(LabelOptions $options): void
    {
        if ($options->visible instanceof Observable) {
            $this->setVisibleObservable($options->visible);
        } else {
            $this->setVisible($options->visible);
        }
    }
}
