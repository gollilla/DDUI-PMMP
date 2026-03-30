<?php

declare(strict_types=1);

namespace soradore\DDUI\properties;

use pocketmine\network\mcpe\protocol\types\StringDataStoreValue;

class StringProperty extends DataDrivenProperty
{
    public function __construct(string $name, string $value, ?ObjectProperty $parent = null)
    {
        parent::__construct($name, $value, $parent);
    }

    public function getValue(): string
    {
        return (string) $this->value;
    }

    public function setValue(mixed $value): void
    {
        $this->value = (string) $value;
    }

    public function toDataStoreValue(): StringDataStoreValue
    {
        return new StringDataStoreValue($this->getValue());
    }

    public function toJsonValue(): string
    {
        return $this->getValue();
    }
}
