<?php

declare(strict_types=1);

namespace soradore\DDUI\element;

use soradore\DDUI\element\options\SpacerOptions;
use soradore\DDUI\Observable;
use soradore\DDUI\properties\BoolProperty;
use soradore\DDUI\properties\ObjectProperty;

class SpacerElement extends Element
{
    public function __construct(
        ?SpacerOptions $options = null,
        ?ObjectProperty $parent = null,
    ) {
        parent::__construct('spacer', $parent);

        $options ??= new SpacerOptions();

        $this->applyVisible($options);
    }

    public function setVisible(bool $visible): static
    {
        parent::setVisible($visible);
        $this->setProperty(new BoolProperty('spacer_visible', $visible, $this));

        return $this;
    }

    public function setVisibleObservable(Observable $visible): static
    {
        parent::setVisibleObservable($visible);
        $property = new BoolProperty('spacer_visible', $visible->getValue(), $this);
        $visible->subscribe(function (bool $v) use ($property): ?BoolProperty {
            $property->setValue($v);

            return $property;
        });
        $this->setProperty($property);

        return $this;
    }

    private function applyVisible(SpacerOptions $options): void
    {
        if ($options->visible instanceof Observable) {
            $this->setVisibleObservable($options->visible);
        } else {
            $this->setVisible($options->visible);
        }
    }
}
