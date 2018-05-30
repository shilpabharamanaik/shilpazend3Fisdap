<?php
namespace Fisdap\Doctrine\Extensions\ColumnType;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Rhumsaa\Uuid\Uuid;

/**
 * Doctrine Column Type that accepts a UUID/GUID value and stores it as binary(16) in MySQL.
 * Use with Fisdap\Doctrine\Extensions\IdGenerator\UuidGenerator strategy for good outcomes.
 * Example Doctrine annotation:
 *
 *  @Id
 *  @Column(type="uuid", length=16)
 *  @GeneratedValue(strategy="CUSTOM")
 *  @CustomIdGenerator(class="Fisdap\Doctrine\Extensions\IdGenerator\UuidGenerator")
 *
 * Note: @GeneratedValue can only be used on one column in a given entity.
 *
 * This column type class does not perform any re-ordering or re-formatting. If you do not use
 * the UuidGenerator id generation strategy, then it is up to the app to determine and handle
 * UUID format in a consistent manner.
 *
 * This class *does* unpack the binary value to hexadecimal string, and vice versa. This happens
 * automatically with entities, but will NOT happen automatically in DQL
 * For DQL you have to pass the type as the third parameter to setParameter()
 *      ->setParameter($key, $value, $type = null)
 *      ->setParameter('shift_id', '11E4C1F813341CB591E7080027880CA6', 'uuid')
 * see also: http://www.doctrine-project.org/jira/browse/DDC-2224
 *
 * (otherwise we'd have to be HEX() and UNHEX()-ing in SQL all the time)
 *
 * @since 2.0
 */
class UuidType extends Type
{
    const UUID = 'uuid';


    /**
     * @inheritdoc
     */
    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getBinaryTypeDeclarationSQL($fieldDeclaration);
    }


    /**
     * @inheritdoc
     */
    public function getName()
    {
        return self::UUID;
    }


    /**
     * @inheritdoc
     */
    public function convertToPhpValue($value, AbstractPlatform $platform)
    {
        if ($value !== null) {
            return strtoupper(bin2hex($value));
        }
    }


    /**
     * @inheritdoc
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value !== null) {
            // If the app put any dashes in, we strip them, just in case
            return hex2bin(str_replace('-', '', $value));
        }
    }


    /**
     * @inheritdoc
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }


    /**
     * Generate a UUID that is optimized for MySQL's InnoDB engine
     * Based on UUID1, but transposed for more optimal inserts and sized for binary(16) column
     *
     * @param int|string $node A 48-bit number representing the hardware
     *                         address. This number may be represented as
     *                         an integer or a hexadecimal string.
     *
     * @return string MySQL-optimized UUID that works well with UUID column type
     */
    public static function generateUuid($node = null)
    {
        $uuid = Uuid::uuid1($node)->toString();

        $uuidFormattedForMySQL = self::transposeUuid($uuid);

        return $uuidFormattedForMySQL;
    }


    /**
     * Optimize format of UUID for storing as binary in MySQL
     *
     * @param $uuid
     *
     * @return string
     *
     * @see http://www.percona.com/blog/2014/12/19/store-uuid-optimized-way/
     */
    public static function transposeUuid($uuid)
    {
        /*
         * orig UUID1: 13341cb5-c1f8-11e4-91e7-080027880ca6
         * transpose:  11e4-c1f8-13341cb5-91e7-080027880ca6
         * format:     11E4C1F813341CB591E7080027880CA6  <-- this is what can be ideally stored as binary in MySQL
         */
        $uuidOptimizedOrderForMySQL = substr($uuid, 14, 4) . substr($uuid, 9, 4) . substr($uuid, 0, 8) . substr(
                $uuid,
                19,
                17
            );
        $uuidFormattedForMySQL = strtoupper(str_replace('-', '', $uuidOptimizedOrderForMySQL));

        return $uuidFormattedForMySQL;
    }
}
