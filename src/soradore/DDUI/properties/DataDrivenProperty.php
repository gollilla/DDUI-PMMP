<?php

declare(strict_types=1);

namespace soradore\DDUI\properties;

use pocketmine\network\mcpe\protocol\types\DataStoreValue;
use pocketmine\player\Player;
use soradore\DDUI\DataDrivenScreen;

abstract class DataDrivenProperty
{
    /** @var callable[] (Player, mixed): void */
    private array $listeners = [];

    private int $triggerCount = 0;

    public function __construct(
        protected string $name,
        protected mixed $value,
        protected ?ObjectProperty $parent,
    ) {}

    abstract public function toDataStoreValue(): DataStoreValue;

    abstract public function toJsonValue(): mixed;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    public function getParent(): ?ObjectProperty
    {
        return $this->parent;
    }

    public function getTriggerCount(): int
    {
        return $this->triggerCount;
    }

    public function addListener(callable $listener): static
    {
        $this->listeners[] = $listener;

        return $this;
    }

    public function triggerListeners(Player $player, mixed $data): void
    {
        $this->triggerCount++;
        foreach ($this->listeners as $listener) {
            ($listener)($player, $data);
        }
    }

    public function getPath(): string
    {
        if ($this->parent === null) {
            return $this->name;
        }

        $parentPath = $this->parent->getPath();

        if ($this->parent->getName() === '') {
            return $this->name;
        }

        if (is_numeric($this->name)) {
            return $parentPath . '[' . $this->name . ']';
        }

        return $parentPath . '.' . $this->name;
    }

    public function getRootScreen(): ?DataDrivenScreen
    {
        if ($this->parent !== null) {
            return $this->parent->getRootScreen();
        }

        return null;
    }
}
