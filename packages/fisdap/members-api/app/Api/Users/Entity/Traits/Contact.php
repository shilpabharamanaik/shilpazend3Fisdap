<?php namespace Fisdap\Api\Users\Entity\Traits;

use Doctrine\ORM\Mapping\Column;

/**
 * Trait Contact
 *
 * @package Fisdap\Api\Users\Entity\Traits
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
trait Contact
{
    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $contact_phone = "";

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $contact_name = "";

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $contact_relation = "";


    /**
     * @return string
     */
    public function getContactPhone()
    {
        return $this->contact_phone;
    }


    /**
     * @return string
     */
    public function getContactName()
    {
        return $this->contact_name;
    }


    /**
     * @return string
     */
    public function getContactRelation()
    {
        return $this->contact_relation;
    }


    /**
     * @param string $value
     *
     * @return $this
     *
     * @deprecated
     */
    public function set_contact_name($value)
    {
        $this->contact_name = $value;
        if ($this->getCurrentRoleName() == "student") {
            $this->getCurrentRoleData()->contact_name = $value;
        }
        return $this;
    }


    /**
     * @param string $contactName
     */
    public function setContactName($contactName)
    {
        $this->contact_name = $contactName;
    }


    /**
     * @param string $value
     *
     * @return $this
     *
     * @deprecated
     */
    public function set_contact_phone($value)
    {
        $this->contact_phone = $value;
        if ($this->getCurrentRoleName() == "student") {
            $this->getCurrentRoleData()->contact_phone = $value;
        }
        return $this;
    }


    /**
     * @param string $contactPhone
     */
    public function setContactPhone($contactPhone)
    {
        $this->contact_phone = $contactPhone;
    }


    /**
     * @param string $value
     *
     * @return $this
     *
     * @deprecated
     */
    public function set_contact_relation($value)
    {
        $this->contact_relation = $value;
        if ($this->getCurrentRoleName() == "student") {
            $this->getCurrentRoleData()->contact_relation = $value;
        }
        return $this;
    }


    /**
     * @param string $contactRelation
     */
    public function setContactRelation($contactRelation)
    {
        $this->contact_relation = $contactRelation;
    }
}
