<?php namespace Fisdap\Api\Users\Entity\Traits;

use Doctrine\ORM\Mapping\Column;


/**
 * Trait Address
 *
 * @package Fisdap\Api\Users\Entity\Traits
 * @author  Ben Getsug     <bgetsug@fisdap.net>
 * @author  Nick Karnick   <nkarnick@fisdap.net>
 * @codeCoverageIgnore
 */
trait Address
{
    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $address = "";

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $city = "";

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $state = "";

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $country = "";

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $zip = "";


    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }


    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }


    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }


    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }


    /**
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }


    /**
     * @param string $value
     *
     * @return $this
     *
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_address($value)
    {
        $this->address = $value;
        if ($this->getCurrentRoleName() == "student") {
            $this->getCurrentRoleData()->address = $value;
        }
        return $this;
    }


    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }


    /**
     * @param string $value
     *
     * @return $this
     *
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_city($value)
    {
        $this->city = $value;
        if ($this->getCurrentRoleName() == "student") {
            $this->getCurrentRoleData()->city = $value;
        }
        return $this;
    }


    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }


    /**
     * @param string $value
     *
     * @return $this
     *
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_state($value)
    {
        $this->state = $value;
        if ($this->getCurrentRoleName() == "student") {
            $this->getCurrentRoleData()->state = $value;
        }
        return $this;
    }


    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @param string $value
     *
     * @return $this
     *
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_country($value)
    {
        $this->country = $value;
        if ($this->getCurrentRoleName() == "student") {
            $this->getCurrentRoleData()->country = $value;
        }
        return $this;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }


    /**
     * @param string $value
     *
     * @return $this
     *
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_zip($value)
    {
        $this->zip = $value;
        if ($this->getCurrentRoleName() == "student") {
            $this->getCurrentRoleData()->zip = $value;
        }
        return $this;
    }


    /**
     * @param string $zip
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }
}