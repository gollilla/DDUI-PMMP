<?php

declare(strict_types=1);

namespace soradore\DDUI\properties;

use pocketmine\network\mcpe\protocol\types\BoolDataStoreValue;

class BoolProperty extends DataDrivenProperty
{
    public function __construct(string $name, bool $value, ?ObjectProperty $parent = null)
    {
        parent::__construct($name, $value, $parent);
    }

    public function getValue(): bool
    {
        return (bool) $this->value;
    }

    public function setValue(mixed $value): void
    {
        $this->value = (bool) $value;
    }

    public function toDataStoreValue(): BoolDataStoreValue
    {
        return new BoolDataStoreValue($this->getValue());
    }

    public function toJsonValue(): bool
    {
        return $this->getValue();
    }
}
