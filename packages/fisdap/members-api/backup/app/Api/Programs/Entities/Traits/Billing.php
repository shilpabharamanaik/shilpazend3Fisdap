<?php namespace Fisdap\Api\Programs\Entities\Traits;

use Doctrine\ORM\Mapping\Column;


/**
 * Class Billing
 *
 * @package Fisdap\Api\Programs\Entities\Traits
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait Billing
{
    /**
     * @var string|null
     * @Column(name="BillingEmail", type="string", nullable=true)
     */
    protected $billing_email = null;

    /**
     * @var string|null
     * @Column(name="BillingContact", type="string", nullable=true)
     */
    protected $billing_contact = null;

    /**
     * @var string|null
     * @Column(name="BillingAddress", type="string", nullable=true)
     */
    protected $billing_address = null;

    /**
     * @var string|null
     * @Column(name="BillingAddress2", type="string", nullable=true)
     */
    protected $billing_address2 = null;

    /**
     * @var string|null
     * @Column(name="BillingAddress3", type="string", nullable=true)
     */
    protected $billing_address3 = null;
    
    /**
     * @var string
     * @Column(name="BillingCity", type="string")
     */
    protected $billing_city = "Unspecified";

    /**
     * @var string
     * @Column(name="BillingState", type="string")
     */
    protected $billing_state = "Unspecified";

    /**
     * @var string
     * @Column(name="BillingZip", type="string")
     */
    protected $billing_zip = "Unspecified";

    /**
     * @Column(name="BillProgramCountry", type="string")
     */
    protected $billing_country = "USA";
    
    /**
     * @var string|null
     * @Column(name="BillingPhone", type="string", nullable=true)
     */
    protected $billing_phone = null;

    /**
     * @var string|null
     * @Column(name="BillingFax", type="string", nullable=true)
     */
    protected $billing_fax = null;


    /**
     * @return string|null
     */
    public function getBillingEmail()
    {
        return $this->billing_email;
    }


    /**
     * @param string $billing_email
     */
    public function setBillingEmail($billing_email)
    {
        $this->billing_email = $billing_email;
    }


    /**
     * @return string|null
     */
    public function getBillingContact()
    {
        return $this->billing_contact;
    }


    /**
     * @param string $billing_contact
     */
    public function setBillingContact($billing_contact)
    {
        $this->billing_contact = $billing_contact;
    }


    /**
     * @return string|null
     */
    public function getBillingAddress()
    {
        return $this->billing_address;
    }


    /**
     * @param string $billing_address
     */
    public function setBillingAddress($billing_address)
    {
        $this->billing_address = $billing_address;
    }


    /**
     * @return string|null
     */
    public function getBillingAddress2()
    {
        return $this->billing_address2;
    }


    /**
     * @param string $billing_address2
     */
    public function setBillingAddress2($billing_address2)
    {
        $this->billing_address2 = $billing_address2;
    }


    /**
     * @return string|null
     */
    public function getBillingAddress3()
    {
        return $this->billing_address3;
    }


    /**
     * @param string $billing_address3
     */
    public function setBillingAddress3($billing_address3)
    {
        $this->billing_address3 = $billing_address3;
    }


    /**
     * @return string
     */
    public function getBillingCity()
    {
        return $this->billing_city;
    }


    /**
     * @param string $billing_city
     */
    public function setBillingCity($billing_city)
    {
        $this->billing_city = $billing_city;
    }


    /**
     * @return string
     */
    public function getBillingState()
    {
        return $this->billing_state;
    }


    /**
     * @param string $billing_state
     */
    public function setBillingState($billing_state)
    {
        $this->billing_state = $billing_state;
    }


    /**
     * @return string
     */
    public function getBillingZip()
    {
        return $this->billing_zip;
    }


    /**
     * @param string $billing_zip
     */
    public function setBillingZip($billing_zip)
    {
        $this->billing_zip = $billing_zip;
    }


    /**
     * @return string
     */
    public function getBillingCountry()
    {
        return $this->billing_country;
    }


    /**
     * @param string $billing_country
     */
    public function setBillingCountry($billing_country)
    {
        $this->billing_country = $billing_country;
    }


    /**
     * @return string|null
     */
    public function getBillingPhone()
    {
        return $this->billing_phone;
    }


    /**
     * @param string $billing_phone
     */
    public function setBillingPhone($billing_phone)
    {
        $this->billing_phone = $billing_phone;
    }


    /**
     * @return string|null
     */
    public function getBillingFax()
    {
        return $this->billing_fax;
    }


    /**
     * @param string $billing_fax
     */
    public function setBillingFax($billing_fax)
    {
        $this->billing_fax = $billing_fax;
    }

    
    /**
     * Copy the program address fields into the billing address fields
     * This only happens during the creation of a new school.
     */
    public function populateBillingAddress()
    {
        $this->billing_address = $this->address;
        $this->billing_address2 = $this->address2;
        $this->billing_address3 = $this->address3;
        $this->billing_city = $this->city;
        $this->billing_state = $this->state;
        $this->billing_zip = $this->zip;
        $this->billing_country = $this->country;
        $this->billing_phone = $this->phone;

        return $this;
    }

    
    public function getBillingFirstName()
    {
        //Parse the name and set it
        $namePieces = preg_split('/\s/', $this->billing_contact);
        return $namePieces[0];
    }
    
    
    public function getBillingLastName()
    {
        //Parse the name and set it
        $namePieces = preg_split('/\s/', $this->billing_contact);
        if (count($namePieces) == 2) {
            return $namePieces[1];
        } else if (count($namePieces) == 3) {
            return $namePieces[2];
        }
        return null;
    }
}
