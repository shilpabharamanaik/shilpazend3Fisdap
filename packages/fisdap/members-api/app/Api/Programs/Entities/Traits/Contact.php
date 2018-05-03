<?php namespace Fisdap\Api\Programs\Entities\Traits;

use Doctrine\ORM\Mapping\Column;
use Fisdap\Entity\InstructorLegacy;
use Fisdap\Entity\User;
use Fisdap\EntityUtils;


/**
 * Class Contact
 *
 * @package Fisdap\Api\Programs\Entities\Traits
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait Contact
{
    /**
     * @todo determine if this is actually an instructor ID
     * @var string
     * @Column(name="ProgramContact", type="string")
     */
    protected $program_contact = "Unspecified";

    /**
     * @var string
     * @Column(name="ContactEmail", type="string")
     */
    protected $contact_email = "Unspecified";


    /**
     * @return string
     */
    public function getProgramContactName()
    {
        $contact = EntityUtils::getEntity('InstructorLegacy', $this->program_contact);
        return $contact->first_name . " " . $contact->last_name;
    }


    /**
     * @return InstructorLegacy
     */
    public function getProgramContact()
    {
        $contact = EntityUtils::getEntity('InstructorLegacy', $this->program_contact);
        return $contact;
    }


    /**
     * @return User
     */
    public function getProgramContactUser()
    {
        $contact = EntityUtils::getEntity('InstructorLegacy', $this->program_contact);
        return $contact->user;
    }


    /**
     * @return string
     */
    public function getContactEmail()
    {
        return $this->contact_email;
    }


    /**
     * @param string $contact_email
     */
    public function setContactEmail($contact_email)
    {
        $this->contact_email = $contact_email;
    }
}
