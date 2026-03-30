<?php

declare(strict_types=1);

namespace soradore\DDUI\element;

use soradore\DDUI\properties\DataDrivenProperty;
use soradore\DDUI\properties\ObjectProperty;

class LayoutElement extends ObjectProperty
{
    public function __construct(?ObjectProperty $parent = null)
    {
        parent::__construct('layout', $parent);
    }

    public function setProperty(DataDrivenProperty $property): void
    {
        $count = count($this->getChildren());
        $property->setName((string) $count);
        parent::setProperty($property);
    }

    public function toJsonValue(): mixed
    {
        $result = [];
        foreach ($this->getChildren() as $child) {
            $result[] = $child->toJsonValue();
        }

        return $result;
    }
}
