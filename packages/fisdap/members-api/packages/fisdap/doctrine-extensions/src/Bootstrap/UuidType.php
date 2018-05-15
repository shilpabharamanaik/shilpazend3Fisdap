<?php
/**
 * Created by PhpStorm.
 * User: jmortenson
 * Date: 3/4/15
 * Time: 4:04 PM
 */

namespace Fisdap\Doctrine\Extensions\Bootstrap;

class UuidType
{
    public static function bootstrap()
    {
        // Doctrine's addType() method allows us to add the column type
        \Doctrine\DBAL\Types\Type::addType('uuid', 'Fisdap\Doctrine\Extensions\ColumnType\UuidType');
    }
}
