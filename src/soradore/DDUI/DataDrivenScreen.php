<?php

declare(strict_types=1);

namespace soradore\DDUI;

use pocketmine\network\mcpe\protocol\ClientboundDataDrivenUIShowScreenPacket;
use soradore\DDUI\Packet\DduiCloseAllScreensPacket;
use soradore\DDUI\Packet\DduiDataStorePacket;
use pocketmine\network\mcpe\protocol\types\BoolDataStoreValue;
use pocketmine\network\mcpe\protocol\types\DataStoreUpdate;
use pocketmine\network\mcpe\protocol\types\DoubleDataStoreValue;
use pocketmine\network\mcpe\protocol\types\StringDataStoreValue;
use pocketmine\player\Player;
use soradore\DDUI\element\LayoutElement;
use soradore\DDUI\properties\DataDrivenProperty;
use soradore\DDUI\properties\ObjectProperty;
use soradore\DDUI\Observable;

abstract class DataDrivenScreen extends ObjectProperty
{
    /** @var array<int, DataDrivenScreen> spl_object_id(Player) => screen */
    private static array $activeScreens = [];

    /** @var array<int, Player> spl_object_id(Player) => Player */
    private array $viewers = [];

    private int $updateCount = 0;

    protected readonly LayoutElement $layout;

    public function __construct()
    {
        parent::__construct('');
        $this->layout = new LayoutElement($this);
        $this->setProperty($this->layout);
    }

    abstract public function getIdentifier(): string;

    abstract public function getDataStoreProperty(): string;

    public function show(Player $player): void
    {
        $old = self::$activeScreens[spl_object_id($player)] ?? null;
        if ($old !== null && $old !== $this) {
            unset($old->viewers[spl_object_id($player)]);
        }

        [$storeName] = explode(':', $this->getIdentifier(), 2);

        $player->getNetworkSession()->sendDataPacket(
            DduiDataStorePacket::create($storeName, $this->getDataStoreProperty(), ++$this->updateCount, $this->toJsonValue()),
        );
        $player->getNetworkSession()->sendDataPacket(
            ClientboundDataDrivenUIShowScreenPacket::create($this->getIdentifier(), 0, null),
        );

        $this->viewers[spl_object_id($player)] = $player;
        self::$activeScreens[spl_object_id($player)] = $this;
    }

    public function close(Player $player): void
    {
        unset($this->viewers[spl_object_id($player)]);
        unset(self::$activeScreens[spl_object_id($player)]);
        $player->getNetworkSession()->sendDataPacket(
            DduiCloseAllScreensPacket::create(0),
        );
    }

    /** @return Player[] */
    public function getViewers(): array
    {
        return array_values($this->viewers);
    }

    public static function getActiveScreen(Player $player): ?self
    {
        return self::$activeScreens[spl_object_id($player)] ?? null;
    }

    public static function removeActiveScreen(Player $player): void
    {
        unset(self::$activeScreens[spl_object_id($player)]);
    }

    /**
     * Handle an incoming ServerboundDataStorePacket update for a player.
     * Resolves the path to the affected property and fires its listeners.
     */
    public static function handleIncoming(Player $player, DataStoreUpdate $update): void
    {
        $screen = self::getActiveScreen($player);
        if ($screen === null) {
            return;
        }

        $property = $screen->resolvePath($update->getPath());
        if ($property === null) {
            return;
        }

        $data = $update->getData();
        $value = match (true) {
            $data instanceof BoolDataStoreValue   => $data->getValue(),
            $data instanceof StringDataStoreValue => $data->getValue(),
            $data instanceof DoubleDataStoreValue => $data->getValue(),
        };

        Observable::withOutboundSuppressed(fn() => $property->triggerListeners($player, $value));
    }

    /**
     * Resolve a dotted/bracket path string to the property at that location.
     * Example: "layout[0].onClick" -> ButtonClickElement
     */
    public function resolvePath(string $path): ?DataDrivenProperty
    {
        if ($path === '') {
            return $this;
        }

        $current = $this;
        $i = 0;
        $len = strlen($path);

        while ($i < $len) {
            $c = $path[$i];

            if ($c === '.') {
                $i++;
                continue;
            }

            if ($c === '[') {
                $end = strpos($path, ']', $i + 1);
                if ($end === false) {
                    return null;
                }
                $token = substr($path, $i + 1, $end - $i - 1);
                $i = $end + 1;
            } else {
                $end = $i;
                while ($end < $len && $path[$end] !== '.' && $path[$end] !== '[') {
                    $end++;
                }
                $token = substr($path, $i, $end - $i);
                $i = $end;
            }

            if (! ($current instanceof ObjectProperty)) {
                return null;
            }

            $current = $current->getProperty($token);
            if ($current === null) {
                return null;
            }
        }

        return $current;
    }

    public function getRootScreen(): ?DataDrivenScreen
    {
        return $this;
    }
}
