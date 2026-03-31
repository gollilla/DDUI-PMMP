<?php

declare(strict_types=1);

namespace soradore\DDUI\element;

use soradore\DDUI\element\options\DividerOptions;
use soradore\DDUI\Observable;
use soradore\DDUI\properties\BoolProperty;
use soradore\DDUI\properties\ObjectProperty;

class DividerElement extends Element
{
    public function __construct(
        ?DividerOptions $options = null,
        ?ObjectProperty $parent = null,
    ) {
        parent::__construct('divider', $parent);

        $options ??= new DividerOptions();

        $this->applyVisible($options);
    }

    public function setVisible(bool $visible): static
    {
        parent::setVisible($visible);
        $this->setProperty(new BoolProperty('divider_visible', $visible, $this));

        return $this;
    }

    public function setVisibleObservable(Observable $visible): static
    {
        parent::setVisibleObservable($visible);
        $property = new BoolProperty('divider_visible', $visible->getValue(), $this);
        $visible->subscribe(function (bool $v) use ($property): ?BoolProperty {
            $property->setValue($v);

            return $property;
        });
        $this->setProperty($property);

        return $this;
    }

    private function applyVisible(DividerOptions $options): void
    {
        if ($options->visible instanceof Observable) {
            $this->setVisibleObservable($options->visible);
        } else {
            $this->setVisible($options->visible);
        }
    }
}
