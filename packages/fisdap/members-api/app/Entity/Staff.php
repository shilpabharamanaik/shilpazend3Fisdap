<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for Fisdap Staff members.
 *
 * @Entity
 * @Table(name="StaffData")
 */
class Staff extends EntityBaseClass
{
    /**
     * @Id
     * @Column(name="id",type="integer")
     * @GeneratedValue
     */
    public $staffId;
    
    /**
     * @Column(type="string")
     */
    protected $username;

    /**
     * @var User
     * @OneToOne(targetEntity="User", inversedBy="staff")
     */
    protected $user;

    /**
     * @Column(name="is_dev", type="integer", nullable=true)
     */
    protected $isDeveloper = 0;
    
    /**
     * @Column(name="is_tech", type="integer", nullable=true)
     */
    protected $isTechnology = 0;
    
    /**
     * @Column(name="is_mgmt", type="integer", nullable=true)
     */
    protected $isManagement = 0;

    
    public function setUsername($username)
    {
        $this->username = $username;
    }
    
    public function getDeveloperStatus()
    {
        return $this->isDeveloper;
    }
    
    public function setDeveloperStatus($status)
    {
        if ($status) {
            $this->isDeveloper = true;
        } else {
            $this->isDeveloper = false;
        }
    }
    
    public function getTechnologyStatus()
    {
        return $this->isTechnology;
    }
    
    public function setTechnologyStatus($status)
    {
        if ($status) {
            $this->isTechnology = true;
        } else {
            $this->isTechnology = false;
        }
    }

    public function getManagementStatus()
    {
        return $this->isManagement;
    }
    
    public function setManagementStatus($status)
    {
        if ($status) {
            $this->isManagement = true;
        } else {
            $this->isManagement = false;
        }
    }


    /**
     * If there is an ID, the user exists in this table, and thus isStaff()
     * @codeCoverageIgnore
     * @deprecated
     */
    public function isStaff()
    {
        if ($this->staffId > 0) {
            return true;
        } else {
            return false;
        }
    }


    //	/**
//	 * @inheritdoc
//	 */
//	public function serialize()
//	{
//		return serialize([
//			'staffId' => $this->staffId,
//			'username' => $this->username
//		]);
//	}
//
//
//	/**
//	 * @inheritdoc
//	 */
//	public function unserialize($serialized)
//	{
//		foreach (unserialize($serialized) as $key => $value) {
//			$this->$key = $value;
//		}
//	}
}
