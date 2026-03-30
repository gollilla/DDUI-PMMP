<?php

declare(strict_types=1);

namespace soradore\DDUI\Packet;

use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pmmp\encoding\LE;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\PacketHandlerInterface;
use pocketmine\network\mcpe\protocol\serializer\CommonTypes;

/**
 * ClientboundDataDrivenUICloseAllScreensPacket (0x14e)
 * Payload: optional IntLE formId
 */
class DduiCloseAllScreensPacket extends DataPacket implements ClientboundPacket
{
    public const NETWORK_ID = 0x14e;

    private ?int $formId = null;

    public static function create(?int $formId = null): static
    {
        $p = new static();
        $p->formId = $formId;

        return $p;
    }

    protected function decodePayload(ByteBufferReader $in): void {}

    protected function encodePayload(ByteBufferWriter $out): void
    {
        if ($this->formId !== null) {
            CommonTypes::putBool($out, true);
            LE::writeSignedInt($out, $this->formId);
        } else {
            CommonTypes::putBool($out, false);
        }
    }

    public function handle(PacketHandlerInterface $handler): bool
    {
        return false;
    }
}
