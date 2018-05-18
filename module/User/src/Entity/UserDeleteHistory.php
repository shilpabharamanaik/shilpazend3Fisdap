<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;


/**
 * Entity class to record the history for deleting user accounts
 * 
 * @Entity
 * @Table(name="fisdap2_user_delete_history")
 */
class UserDeleteHistory extends EntityBaseClass
{
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;
    
    /**
     * @var User
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="deleted_user_id", referencedColumnName="id")
     */
    protected $deleted_user;
    
    /**
     * @var User
     * @ManyToOne(targetEntity="User")
     */
    protected $user;
    
    /**
     * @var ProgramLegacy
     * @ManyToOne(targetEntity="ProgramLegacy")
	 * @JoinColumn(name="program_id", referencedColumnName="Program_id")
     */
    protected $program;
    
    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $deletion_date;
    
    public function init()
    {
        $this->deletion_date = new \DateTime();
    }
    
    public function set_deleted_user($value)
    {
        $this->deleted_user = self::id_or_entity_helper($value, "User");
        return $this;
    }
    
    public function set_user($value)
    {
        $this->user = self::id_or_entity_helper($value, "User");
        $this->program = $this->user->getCurrentProgram();
        return $this;
    }
    
    public function set_program($value)
    {
        $this->program = self::id_or_entity_helper($value, "ProgramLegacy");
        return $this;
    }
}