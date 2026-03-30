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
 * Encodes ClientboundDataStorePacket in PNX wire format:
 *   - updateCount: IntLE (not VarInt)
 *   - value type IDs: IntLE (not VarInt)
 *   - value type: TYPE(6) recursive object (not STRING JSON)
 *   - INT64 type ID = 2, STRING type ID = 4, BOOL type ID = 1, OBJECT type ID = 6
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
    private int $triggerCount = 0;

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
    public static function createUpdate(
        string $storeName,
        string $property,
        int $updateCount,
        string $path,
        int $triggerCount,
        mixed $value,
    ): static {
        $p = new static();
        $p->storeName = $storeName;
        $p->property = $property;
        $p->updateCount = $updateCount;
        $p->isUpdate = true;
        $p->path = $path;
        $p->triggerCount = $triggerCount;
        $p->value = $value;

        return $p;
    }

    protected function decodePayload(ByteBufferReader $in): void {}

    protected function encodePayload(ByteBufferWriter $out): void
    {
        VarInt::writeUnsignedInt($out, 1); // 1 entry

        if ($this->isUpdate) {
            VarInt::writeUnsignedInt($out, 2); // DataStoreType::UPDATE = 2
            CommonTypes::putString($out, $this->storeName);
            CommonTypes::putString($out, $this->property);
            LE::writeUnsignedInt($out, $this->updateCount);
            CommonTypes::putString($out, $this->path);
            LE::writeUnsignedInt($out, $this->triggerCount);
        } else {
            VarInt::writeUnsignedInt($out, 1); // DataStoreType::CHANGE = 1
            CommonTypes::putString($out, $this->storeName);
            CommonTypes::putString($out, $this->property);
            LE::writeUnsignedInt($out, $this->updateCount);
        }

        $this->writePnxTypedValue($out, $this->value);
    }

    private function writePnxTypedValue(ByteBufferWriter $out, mixed $value): void
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
            $this->writePnxObject($out, $value);
        }
    }

    private function writePnxObject(ByteBufferWriter $out, array $value): void
    {
        if (array_is_list($value)) {
            // Sequential array (e.g. layout, items): add numeric string keys + "length"
            VarInt::writeUnsignedInt($out, count($value) + 1);
            foreach ($value as $i => $child) {
                CommonTypes::putString($out, (string) $i);
                $this->writePnxTypedValue($out, $child);
            }
            CommonTypes::putString($out, 'length');
            LE::writeUnsignedInt($out, 2); // INT64
            LE::writeSignedLong($out, count($value));
        } else {
            // Associative object
            VarInt::writeUnsignedInt($out, count($value));
            foreach ($value as $key => $child) {
                CommonTypes::putString($out, (string) $key);
                $this->writePnxTypedValue($out, $child);
            }
        }
    }

    public function handle(PacketHandlerInterface $handler): bool
    {
        return false;
    }
}
