<?php

declare(strict_types=1);

namespace soradore\DDUI\properties;

use pocketmine\network\mcpe\protocol\types\DoubleDataStoreValue;

class FloatProperty extends DataDrivenProperty
{
    public function __construct(string $name, float $value, ?ObjectProperty $parent = null)
    {
        parent::__construct($name, $value, $parent);
    }

    public function getValue(): float
    {
        return (float) $this->value;
    }

    public function setValue(mixed $value): void
    {
        $this->value = (float) $value;
    }

    public function toDataStoreValue(): DoubleDataStoreValue
    {
        return new DoubleDataStoreValue($this->getValue());
    }

    public function toJsonValue(): float
    {
        return $this->getValue();
    }
}
