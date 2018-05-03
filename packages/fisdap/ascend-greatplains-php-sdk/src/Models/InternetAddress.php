<?php namespace Fisdap\Ascend\Greatplains\Models;

use Fisdap\Ascend\Greatplains\Contracts\InternetAddress as InternetAddressInterface;
use Fisdap\Ascend\Greatplains\Exceptions\InvalidArgumentException;

/**
 * Class InternetAddress
 *
 * Object to represent an individual internet address
 *
 * @package Fisdap\Ascend\Greatplains\Models
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class InternetAddress implements InternetAddressInterface
{
    /**
     * The type of the internet address being created
     *
     * @var string
     */
    protected $type;

    /**
     * The value of the internet address, such as test@test.com
     *
     * @var string
     */
    protected $value;

    /**
     * Acceptable type of internet addresses allowed
     *
     * @var array
     */
    public static $types = [
        self::ADDITIONAL_INFORMATION_TYPE,
        self::EMAIL_BCC_ADDRESS_TYPE,
        self::EMAIL_CC_ADDRESS_TYPE,
        self::EMAIL_TO_ADDRESS_TYPE,
        self::INTERNET_FIELD_1_TYPE,
        self::INTERNET_FIELD_2_TYPE,
        self::INTERNET_FIELD_3_TYPE,
        self::INTERNET_FIELD_4_TYPE,
        self::INTERNET_FIELD_5_TYPE,
        self::INTERNET_FIELD_6_TYPE,
        self::INTERNET_FIELD_7_TYPE,
        self::INTERNET_FIELD_8_TYPE,
        self::MESSENGER_ADDRESS_TYPE
    ];

    /**
     * InternetAddress constructor.
     * @param string $type
     * @param string $value
     * @throws InvalidArgumentException
     */
    public function __construct($type, $value)
    {
        if (!$this->validateType($type)) {
            throw new InvalidArgumentException("Invalid internet address type passed when creating internet address");
        }

        $this->type = $type;
        $this->value = $value;
    }

    /**
     * Check to see if the type of internet address being created is valid
     *
     * @param string $type
     * @return bool
     */
    protected function validateType($type)
    {
        return in_array($type, self::$types);
    }

    /**
     * Get internet address type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get the internet address value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
