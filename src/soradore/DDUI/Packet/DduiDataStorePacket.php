<?php

declare(strict_types=1);

namespace soradore\DDUI\Packet;

use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pmmp\encoding\LE;
use pmmp\encoding\VarInt;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\PacketHandlerInterface;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\serializer\CommonTypes;

/**
 * Encodes ClientboundDataStorePacket in PNX wire format.
 *
 * Type ordinals (DataStoreChangeInfo.Type):
 *   UPDATE = 0
 *   CHANGE = 1
 *
 * CHANGE payload:
 *   String storeName, String property, IntLE updateCount,
 *   IntLE valueTypeId, [value]
 *
 * UPDATE payload:
 *   String storeName, String property, String path,
 *   VarInt control (0=INT64, 1=BOOL, 2=STRING), [value]
 *
 * Value type IDs (DataStorePropertyValue.Type):
 *   BOOL=1, INT64=2, STRING=4, TYPE(object)=6
 */
class DduiDataStorePacket extends DataPacket implements ClientboundPacket
{
    public const NETWORK_ID = ProtocolInfo::CLIENTBOUND_DATA_STORE_PACKET;

    private string $storeName;
    private string $property;
    private int $updateCount;
    /** @var mixed PHP value tree from toJsonValue() */
    private mixed $value;

    private bool $isUpdate = false;
    private string $path = '';

    /** @param mixed $value Result of DataDrivenScreen::toJsonValue() */
    public static function create(string $storeName, string $property, int $updateCount, mixed $value): static
    {
        $p = new static();
        $p->storeName = $storeName;
        $p->property = $property;
        $p->updateCount = $updateCount;
        $p->value = $value;

        return $p;
    }

    /** @param mixed $value Result of DataDrivenProperty::toJsonValue() */
    public static function createUpdate(string $storeName, string $property, string $path, mixed $value): static
    {
        $p = new static();
        $p->storeName = $storeName;
        $p->property = $property;
        $p->isUpdate = true;
        $p->path = $path;
        $p->value = $value;

        return $p;
    }

    protected function decodePayload(ByteBufferReader $in): void {}

    protected function encodePayload(ByteBufferWriter $out): void
    {
        VarInt::writeUnsignedInt($out, 1); // 1 entry

        if ($this->isUpdate) {
            VarInt::writeUnsignedInt($out, 0); // UPDATE = 0
            CommonTypes::putString($out, $this->storeName);
            CommonTypes::putString($out, $this->property);
            CommonTypes::putString($out, $this->path);
            $this->writeUpdateValue($out, $this->value);
        } else {
            VarInt::writeUnsignedInt($out, 1); // CHANGE = 1
            CommonTypes::putString($out, $this->storeName);
            CommonTypes::putString($out, $this->property);
            LE::writeUnsignedInt($out, $this->updateCount);
            $this->writeChangeValue($out, $this->value);
        }
    }

    /**
     * CHANGE value: IntLE typeId first, then value bytes.
     */
    private function writeChangeValue(ByteBufferWriter $out, mixed $value): void
    {
        if (is_bool($value)) {
            LE::writeUnsignedInt($out, 1); // BOOL
            CommonTypes::putBool($out, $value);
        } elseif (is_int($value) || is_float($value)) {
            LE::writeUnsignedInt($out, 2); // INT64
            LE::writeSignedLong($out, (int) $value);
        } elseif (is_string($value)) {
            LE::writeUnsignedInt($out, 4); // STRING
            CommonTypes::putString($out, $value);
        } elseif (is_array($value)) {
            LE::writeUnsignedInt($out, 6); // TYPE (object)
            $this->writeChangeObject($out, $value);
        }
    }

    private function writeChangeObject(ByteBufferWriter $out, array $value): void
    {
        if (array_is_list($value)) {
            // Sequential array: numeric string keys + "length"
            VarInt::writeUnsignedInt($out, count($value) + 1);
            foreach ($value as $i => $child) {
                CommonTypes::putString($out, (string) $i);
                $this->writeChangeValue($out, $child);
            }
            CommonTypes::putString($out, 'length');
            LE::writeUnsignedInt($out, 2); // INT64
            LE::writeSignedLong($out, count($value));
        } else {
            // Associative object
            VarInt::writeUnsignedInt($out, count($value));
            foreach ($value as $key => $child) {
                CommonTypes::putString($out, (string) $key);
                $this->writeChangeValue($out, $child);
            }
        }
    }

    /**
     * UPDATE value: VarInt control (0=INT64, 1=BOOL, 2=STRING), then value bytes,
     * then IntLE propertyUpdateCount, IntLE pathUpdateCount.
     */
    private function writeUpdateValue(ByteBufferWriter $out, mixed $value): void
    {
        if (is_bool($value)) {
            VarInt::writeUnsignedInt($out, 1); // BOOL
            CommonTypes::putBool($out, $value);
        } elseif (is_string($value)) {
            VarInt::writeUnsignedInt($out, 2); // STRING
            CommonTypes::putString($out, $value);
        } else {
            VarInt::writeUnsignedInt($out, 0); // INT64 (written as doubleLE)
            // Write float as little-endian double (8 bytes) by reinterpreting bits
            LE::writeSignedLong($out, unpack('q', pack('e', (float) $value))[1]);
        }
        LE::writeUnsignedInt($out, 1); // propertyUpdateCount
        LE::writeUnsignedInt($out, 1); // pathUpdateCount
    }

    public function handle(PacketHandlerInterface $handler): bool
    {
        return false;
    }
}
