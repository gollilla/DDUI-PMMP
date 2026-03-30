<?php

declare(strict_types=1);

namespace soradore\DDUI\Listener;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketDecodeEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ServerboundDataStorePacket;
use soradore\DDUI\DataDrivenScreen;
use soradore\DDUI\Packet\DduiClosedPacket;

class DduiListener implements Listener
{
    /** @handleCancelled */
    public function onDataPacketDecode(DataPacketDecodeEvent $event): void
    {
        $id = $event->getPacketId();
        if ($id === ServerboundDataStorePacket::NETWORK_ID || $id === DduiClosedPacket::NETWORK_ID) {
            $event->uncancel();
        }
    }

    public function onDataPacketReceive(DataPacketReceiveEvent $event): void
    {
        $packet = $event->getPacket();
        $player = $event->getOrigin()->getPlayer();
        if ($player === null) {
            return;
        }

        if ($packet instanceof ServerboundDataStorePacket) {
            DataDrivenScreen::handleIncoming($player, $packet->getUpdate());

            return;
        }

        if ($packet instanceof DduiClosedPacket) {
            DataDrivenScreen::getActiveScreen($player)?->close($player);
        }
    }
}
