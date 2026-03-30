<?php

declare(strict_types=1);

namespace soradore\DDUI;

use soradore\DDUI\Packet\DduiDataStorePacket;
use soradore\DDUI\properties\DataDrivenProperty;

/**
 * A reactive value holder that notifies subscribers when the value changes
 * and optionally propagates live updates to connected clients via DataStore packets.
 *
 * Listener signature: callable(mixed $newValue): ?DataDrivenProperty
 * Return the DataDrivenProperty that was updated, or null to skip outbound.
 *
 * @template T
 */
class Observable
{
    /** @var callable[] */
    private array $listeners = [];

    private static bool $suppressOutbound = false;

    /** @param T $value */
    public function __construct(private mixed $value) {}

    /** @return T */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /** @param T $value */
    public function setValue(mixed $value): void
    {
        $this->value = $value;

        if (self::$suppressOutbound) {
            foreach ($this->listeners as $listener) {
                ($listener)($value);
            }

            return;
        }

        foreach ($this->listeners as $listener) {
            $property = ($listener)($value);

            if ($property === null) {
                continue;
            }

            $screen = $property->getRootScreen();
            if ($screen === null) {
                continue;
            }

            [$storeName] = explode(':', $screen->getIdentifier(), 2);
            $packet = DduiDataStorePacket::createUpdate(
                $storeName,
                $screen->getDataStoreProperty(),
                $screen->getUpdateCount(),
                $property->getPath(),
                $property->getTriggerCount(),
                $property->toJsonValue(),
            );

            foreach ($screen->getViewers() as $viewer) {
                $viewer->getNetworkSession()->sendDataPacket($packet);
            }
        }
    }

    public function subscribe(callable $listener): void
    {
        $this->listeners[] = $listener;
    }

    public function unsubscribe(callable $listener): void
    {
        $this->listeners = array_filter(
            $this->listeners,
            static fn($l) => $l !== $listener,
        );
    }

    /**
     * Run $action with outbound DataStore packets suppressed.
     * Use this when applying client-originated updates back to observables
     * to avoid sending redundant packets.
     */
    public static function withOutboundSuppressed(callable $action): void
    {
        $prev = self::$suppressOutbound;
        self::$suppressOutbound = true;
        try {
            $action();
        } finally {
            self::$suppressOutbound = $prev;
        }
    }
}
