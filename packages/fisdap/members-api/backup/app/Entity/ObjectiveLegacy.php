<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * Legacy Entity class for Discounts.
 *
 * @Entity(repositoryClass="Fisdap\Data\Objective\DoctrineObjectiveLegacyRepository")
 * @Table(name="Objective_def")
 */
class ObjectiveLegacy extends EntityBaseClass
{
    /**
     * @Id
     * @Column(name="ObjectiveDef_id", type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @var string
     * @Column(name="ObjectiveName", type="string")
     */
    protected $name;
    
    /**
     * @var integer
     * @Column(name="CatDef_id", type="integer")
     */
    protected $cat_def_id = -1;
    
    /**
     * @var string
     * @Column(name="Description", type="string", nullable=true)
     */
    protected $description;
    
    /**
     * @var integer
     * @Column(name="Domain_id", type="integer")
     */
    protected $domain_id = -1;
    
    /**
     * @var integer
     * @Column(name="Level_id", type="integer")
     */
    protected $level_id = -1;
    
    /**
     * @var integer
     * @Column(name="WIS", type="integer", nullable=true)
     */
    protected $wis;
    
    /**
     * @var string
     * @Column(name="Module", type="string", nullable=true)
     */
    protected $module;
    
    /**
     * @var integer
     * @Column(name="emr_depth", type="integer")
     */
    protected $emr_depth = -1;
    
    /**
     * @var integer
     * @Column(name="emr_breadth", type="integer")
     */
    protected $emr_breadth = -1;
    
    /**
     * @var integer
     * @Column(name="emt_depth", type="integer")
     */
    protected $emt_depth = -1;
    
    /**
     * @var integer
     * @Column(name="emt_breadth", type="integer")
     */
    protected $emt_breadth = -1;
    
    /**
     * @var integer
     * @Column(name="aemt_depth", type="integer")
     */
    protected $aemt_depth = -1;
    
    /**
     * @var integer
     * @Column(name="aemt_breadth", type="integer")
     */
    protected $aemt_breadth = -1;
    
    /**
     * @var integer
     * @Column(name="para_depth", type="integer")
     */
    protected $para_depth = -1;
    
    /**
     * @var integer
     * @Column(name="para_breadth", type="integer")
     */
    protected $para_breadth = -1;
}
