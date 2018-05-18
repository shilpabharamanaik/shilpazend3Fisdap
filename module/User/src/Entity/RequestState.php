<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;


/**
 * Request State
 * 
 * @Entity
 * @Table(name="fisdap2_request_state")
 */
class RequestState extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @var string
     * @Column(type="string")
     */
    protected $name;
    
    /**
     * @var string
     * @Column(type="string")
     */
    protected $action;
}