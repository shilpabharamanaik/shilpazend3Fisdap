<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\Table;


/**
 * Todo
 *
 * Todo is a Message that has user-modifiable attributes like a Completed flag and Due date
 * 
 * @Entity
 * @Table(name="fisdap2_todos")
 * @HasLifecycleCallbacks
 */
class Todo extends EntityBaseClass
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @Column(type="text", nullable=true)
     */
    protected $notes;
    
    /**
     * @Column(type="boolean")
     */
    protected $completed;
    
    
    /*
     * Lifecycle Callbacks
     */
    
    /**
     * @PrePersist
     */
    public function created()
    {
        // set default for completed
        if (!isset($this->completed)) {
            $this->completed = 0;
        }
    }
    
    /*
     * Setters
     */

    public function set_notes($text) {
        $this->notes = $text; //@todo any format checking we need to do here?
    }
    
    public function set_completed($completed) {
        $this->completed = ($completed) ? 1 : 0;
    }
    
    
    /*
     * Getters
     */

    public function get_notes() {
        return $this->notes;
    }
    
    public function get_completed() {
        return ($this->completed) ? TRUE : FALSE;
    }
    
}
