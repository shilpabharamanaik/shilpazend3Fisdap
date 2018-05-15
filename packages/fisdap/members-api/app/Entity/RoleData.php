<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;

/**
 * A base class for Role Data subclasses to extend from.
 *
 * @author astevenson, bgetsug
 */
class RoleData extends EntityBaseClass
{
    /**
     * @var UserContext
     * @OneToOne(targetEntity="UserContext", cascade={"detach"}, fetch="EAGER")
     * @JoinColumn(name="user_role_id", referencedColumnName="id")
     */
    protected $user_context;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @var User
     * @OneToOne(targetEntity="User", cascade={"detach"}, fetch="EAGER")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="UserName", type="string", nullable=false)
     */
    protected $username;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="FirstName", type="string", nullable=false)
     */
    protected $first_name;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="LastName", type="string", nullable=false)
     */
    protected $last_name;


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @codeCoverageIgnore
     * @deprecated
     * @return string
     */
    public function getFullName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }


    /**
     * @codeCoverageIgnore
     * @deprecated
     * @return string
     */
    public function getLastFirstName()
    {
        return $this->last_name . ', ' . $this->first_name;
    }


    /**
     * @return UserContext
     */
    public function getUserContext()
    {
        return $this->user_context;
    }


    /**
     * @param UserContext $userContext
     */
    public function setUserContext(UserContext $userContext)
    {
        $this->user_context = $userContext;
    }


    /**
     * @codeCoverageIgnore
     * @deprecated User should be accessed directly or via UserContext
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }


    /**
     * @codeCoverageIgnore
     * @deprecated
     * @todo remove direct association with User
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }


    /**
     * @codeCoverageIgnore
     * @deprecated use UserContext Program association instead
     * @todo remove direct association with Program
     * @param ProgramLegacy $programLegacy
     */
    public function setProgram(ProgramLegacy $programLegacy)
    {
        $this->program = $programLegacy;
    }


    /**
     * @codeCoverageIgnore
     * @deprecated
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }


    /**
     * @codeCoverageIgnore
     * @deprecated
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;
    }


    /**
     * @codeCoverageIgnore
     * @deprecated
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;
    }


    /**
     * @codeCoverageIgnore
     * @deprecated
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Activate a serial number and tie it to this role data entity
     *
     * @param \Fisdap\Entity\SerialNumberLegacy
     * @return \Fisdap\Entity\RoleData
     */
    public function activateSerialNumber(SerialNumberLegacy $serial)
    {
        $serial->activation_date = new \DateTime();
        $serial->user = $this->getUser();
        $serial->setUserContext($this->getUserContext());
        $serial->applyExtras();

        return $this;
    }
}
