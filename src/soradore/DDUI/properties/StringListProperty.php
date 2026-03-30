<?php

declare(strict_types=1);

namespace soradore\DDUI\properties;

use pocketmine\network\mcpe\protocol\types\StringDataStoreValue;

class StringListProperty extends DataDrivenProperty
{
    /** @param string[] $items */
    public function __construct(string $name, array $items, ?ObjectProperty $parent = null)
    {
        parent::__construct($name, array_values($items), $parent);
    }

    public function toDataStoreValue(): StringDataStoreValue
    {
        return new StringDataStoreValue(json_encode($this->value, JSON_THROW_ON_ERROR));
    }

    /** @return string[] */
    public function toJsonValue(): mixed
    {
        return $this->value;
    }
}
