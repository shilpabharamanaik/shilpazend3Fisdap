<?php namespace Fisdap\Api\Users\Entity\Traits;

use Doctrine\ORM\Mapping\Column;


/**
 * Trait Licenses
 *
 * @package Fisdap\Api\Users\Entity\Traits
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
trait Licenses
{
    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $license_number;

    /**
     * @var \DateTime
     * @Column(type="date", nullable=true)
     */
    protected $license_expiration_date;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $license_state;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $state_license_number;

    /**
     * @var \DateTime
     * @Column(type="date", nullable=true)
     */
    protected $state_license_expiration_date;


    /**
     * @return string
     */
    public function getLicenseNumber()
    {
        return $this->license_number;
    }


    /**
     * @return \DateTime
     */
    public function getLicenseExpirationDate()
    {
        return $this->license_expiration_date;
    }


    /**
     * @return string
     */
    public function getLicenseState()
    {
        return $this->license_state;
    }


    /**
     * @return string
     */
    public function getStateLicenseNumber()
    {
        return $this->state_license_number;
    }


    /**
     * @return \DateTime
     */
    public function getStateLicenseExpirationDate()
    {
        return $this->state_license_expiration_date;
    }


    /**
     * @param string $license_number
     */
    public function setLicenseNumber($license_number)
    {
        $this->license_number = $license_number;
    }


    /**
     * @param \DateTime $dateTime
     */
    public function setLicenseExpirationDate(\DateTime $dateTime = null)
    {
        $this->license_expiration_date = $dateTime;
    }


    /**
     * @param string $license_state
     */
    public function setLicenseState($license_state)
    {
        $this->license_state = $license_state;
    }


    /**
     * @param string $state_license_number
     */
    public function setStateLicenseNumber($state_license_number)
    {
        $this->state_license_number = $state_license_number;
    }


    /**
     * @param \DateTime $dateTime
     */
    public function setStateLicenseExpirationDate(\DateTime $dateTime = null)
    {
        $this->state_license_expiration_date = $dateTime;
    }


    /**
     * Set the value of the state license expiration date
     * @param \DateTime|string $value
     * @return $this
     * @deprecated
     */
    public function set_state_license_expiration_date($value)
    {
        if ($value instanceof \DateTime) {
            $this->state_license_expiration_date = $value;
        } else if ($value) {
            $this->state_license_expiration_date = new \DateTime($value);
        }

        return $this;
    }


    /**
     * Set the value of the NREMT license expiration date
     * @param \DateTime|string $value
     * @return $this
     * @deprecated
     */
    public function set_license_expiration_date($value)
    {
        if ($value instanceof \DateTime) {
            $this->license_expiration_date = $value;
        } else if ($value) {
            $this->license_expiration_date = new \DateTime($value);
        }

        return $this;
    }
}