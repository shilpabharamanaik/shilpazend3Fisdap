<?php namespace Fisdap\Api\Programs\Entities\Traits;

use Doctrine\ORM\Mapping\Column;

/**
 * Class Address
 *
 * @package Fisdap\Api\Programs\Entities\Traits
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait Address
{
    /**
     * @var string
     * @Column(name="ProgramAddress", type="string")
     */
    protected $address = "Unspecified";

    /**
     * @var string|null
     * @Column(name="ProgramAddress2", type="string", nullable=true)
     */
    protected $address2 = null;

    /**
     * @var string|null
     * @Column(name="ProgramAddress3", type="string", nullable=true)
     */
    protected $address3 = null;
    
    /**
     * @var string
     * @Column(name="ProgramCity", type="string")
     */
    protected $city = "Unspecified";

    /**
     * @var string
     * @Column(name="ProgramState", type="string")
     */
    protected $state = "Unspecified";

    /**
     * @var string
     * @Column(name="ProgramZip", type="string")
     */
    protected $zip = "Unspecified";

    /**
     * @var string
     * @Column(name="ProgramCountry", type="string")
     */
    protected $country = "USA";


    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }


    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }


    /**
     * @return string|null
     */
    public function getAddress2()
    {
        return $this->address2;
    }


    /**
     * @param string $address2
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;
    }


    /**
     * @return string|null
     */
    public function getAddress3()
    {
        return $this->address3;
    }


    /**
     * @param string $address3
     */
    public function setAddress3($address3)
    {
        $this->address3 = $address3;
    }


    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }


    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }


    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }


    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }


    /**
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }


    /**
     * @param string $zip
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }


    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }


    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }
}
