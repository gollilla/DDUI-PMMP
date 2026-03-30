<?php

declare(strict_types=1);

namespace soradore\DDUI\properties;

use pocketmine\network\mcpe\protocol\types\StringDataStoreValue;
use soradore\DDUI\DataDrivenScreen;

class ObjectProperty extends DataDrivenProperty
{
    /** @var array<string, DataDrivenProperty> */
    private array $children = [];

    public function __construct(string $name, ?ObjectProperty $parent = null)
    {
        parent::__construct($name, null, $parent);
    }

    public function setProperty(DataDrivenProperty $property): void
    {
        $this->children[$property->getName()] = $property;
    }

    public function getProperty(string $name): ?DataDrivenProperty
    {
        return $this->children[$name] ?? null;
    }

    /** @return array<string, DataDrivenProperty> */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function toDataStoreValue(): StringDataStoreValue
    {
        return new StringDataStoreValue(json_encode($this->toJsonValue(), JSON_THROW_ON_ERROR));
    }

    public function toJsonValue(): mixed
    {
        $result = [];
        foreach ($this->children as $key => $child) {
            $result[$key] = $child->toJsonValue();
        }

        return $result;
    }

    public function getRootScreen(): ?DataDrivenScreen
    {
        if ($this instanceof DataDrivenScreen) {
            return $this;
        }

        return parent::getRootScreen();
    }
}
