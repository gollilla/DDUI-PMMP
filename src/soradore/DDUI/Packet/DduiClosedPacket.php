<?php

declare(strict_types=1);

namespace soradore\DDUI\Packet;

use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pmmp\encoding\LE;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\PacketHandlerInterface;
use pocketmine\network\mcpe\protocol\ServerboundPacket;
use pocketmine\network\mcpe\protocol\serializer\CommonTypes;

/**
 * ServerboundDataDrivenClosedPacket (0x157)
 * Sent by client when the native × button is pressed on a DDUI screen.
 * Payload: IntLE formId + String closeReason (e.g. "clientcanceled")
 */
class DduiClosedPacket extends DataPacket implements ServerboundPacket
{
    public const NETWORK_ID = 0x157;

    private int $formId;
    private string $closeReason;

    public function getFormId(): int
    {
        return $this->formId;
    }

    public function getCloseReason(): string
    {
        return $this->closeReason;
    }

    protected function decodePayload(ByteBufferReader $in): void
    {
        $this->formId = LE::readSignedInt($in);
        $this->closeReason = CommonTypes::getString($in);
    }

    protected function encodePayload(ByteBufferWriter $out): void {}

    public function handle(PacketHandlerInterface $handler): bool
    {
        return false;
    }
}
