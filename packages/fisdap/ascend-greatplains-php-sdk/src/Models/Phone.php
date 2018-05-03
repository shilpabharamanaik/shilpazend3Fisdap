<?php namespace Fisdap\Ascend\Greatplains\Models;

use Fisdap\Ascend\Greatplains\Contracts\Phone as PhoneInterface;

/**
 * Class Phone
 *
 * Object representing a phone
 *
 * @package Fisdap\Ascend\Greatplains\Models
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
class Phone implements PhoneInterface
{
    /**
     * @var string
     */
    private $value = '';

    /**
     * @var integer
     */
    private $countryCode = 1;

    /**
     * @var string|null
     */
    private $extension = null;

    /**
     * Phone constructor
     *
     * @param string $value
     * @param integer $countryCode
     * @param string|null $extension
     */
    public function __construct($value, $countryCode = 1, $extension = null)
    {
        $this->setValue($value);

        $this->countryCode = $countryCode;

        if (!is_null($extension)) {
            $this->extension = $extension;
        }
    }

    /**
     * Set the value of the phone number, and if the number is long enough it sets an extension
     *
     * @param string $phone
     * @return $this
     */
    protected function setValue($phone)
    {
        $escapedValue = trim(preg_replace("/[^0-9]/", "", $phone));

        // first 10 digits are phone number
        $this->value = substr($escapedValue, 0, 10);

        // everything else is an extension
        if (strlen($escapedValue) > 10) {
            $this->extension = substr($escapedValue, 10);
        }

        return $this;
    }

    /**
     * Get phone value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get country code
     *
     * @return int
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * Get the extension if exists
     *
     * @return null|string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Get if the object has value
     *
     * @return bool
     */
    public function getHasValue()
    {
        if ($this->getValue()) {
            return true;
        }

        return false;
    }

    /**
     * Get phone object as array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::VALUE_FIELD        => $this->getValue(),
            self::COUNTRY_CODE_FIELD => $this->getCountryCode(),
            self::EXTENSION_FIELD    => $this->getExtension(),
            self::HAS_VALUE_FIELD    => $this->getHasValue(),
        ];
    }
}
