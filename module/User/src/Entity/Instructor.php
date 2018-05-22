<?php

namespace User\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * This class represents a registered user role.
 * @ORM\Entity()
 * @ORM\Table(name="InstructorData")
 */
class Instructor
{

    /**
     * @ORM\Id
     * @ORM\Column(name="Instructor_id")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(name="FirstName")
     */
    protected $firstName;

    /**
     * @ORM\Column(name="LastName")
     */
    protected $lastName;

    /**
     * @ORM\Column(name="UserName")
     */
    protected $userName;

    /**
     * @ORM\Column(name="ProgramId")
     */
    protected $programId;

    /**
     * @ORM\Column(name="Email")
     */
    protected $email;

    /**
     * @ORM\Column(name="EmailEventFlag")
     */
    protected $emailEventFlag;

    /**
     * @ORM\Column(name="PrimaryContact")
     */
    protected $primaryContact;

    /**
     * @ORM\Column(name="EmailList")
     */
    protected $emailList;

    /**
     * @ORM\Column(name="OfficePhone")
     */
    protected $officePhone;

    /**
     * @ORM\Column(name="CellPhone")
     */
    protected $cellPhone;

    /**
     * @ORM\Column(name="Pager")
     */
    protected $pager;

    /**
     * @ORM\Column(name="BigBroEmails")
     */
    protected $bigBroEmails;

    /**
     * @ORM\Column(name="ClinicalBigBroEmails")
     */
    protected $clinicalBigBroEmails;

    /**
     * @ORM\Column(name="Permissions")
     */
    protected $permissions;

    /**
     * @ORM\Column(name="AcceptedAgreement")
     */
    protected $scceptedAgreement;

    /**
     * @ORM\Column(name="LabBigBroEmails")
     */
    protected $labBigBroEmails;

    /**
     * @ORM\Column(name="Reviewer")
     */
    protected $reviewer;

    /**
     * @ORM\Column(name="ActiveReviewer")
     */
    protected $activeReviewer;

    /**
     * @ORM\Column(name="ReviewerNotes")
     */
    protected $reviewerNotes;

    /**
     * @ORM\Column(name="user_id")
     */
    protected $userId;

    /**
     * @ORM\Column(name="user_role_id")
     */
    protected $userRoleId;


    /**
     * @return int
     */
    public function getInstructorId()
    {
        return $this->id;
    }


    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getWorkPhone()
    {
        return $this->officePhone;
    }

    public function getCellPhone()
    {
        return $this->cellPhone;
    }

    public function getHomePhone()
    {
        return $this->pager;
    }

}
