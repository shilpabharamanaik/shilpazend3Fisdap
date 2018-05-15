<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * MyFisdapWidgetData
 *
 * @Entity
 * @Table(name="fisdap2_my_fisdap_widget_definition")
 * @HasLifecycleCallbacks
 */
class MyFisdapWidgetDefinition extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @Column(type="string")
     */
    protected $class_name;
    
    /**
     * @Column(type="text")
     */
    protected $description;
    
    /**
     * @Column(type="string")
     */
    protected $display_title;
    
    /**
     * @Column(type="integer")
     */
    protected $minimum_container_width;
    
    /**
     * @Column(type="boolean")
     */
    protected $is_minimizable = true;
    
    /**
     * @Column(type="text", nullable=true)
     */
    protected $default_data = array();
    
    /**
     * @Column(type="boolean")
     */
    protected $has_configuration = 0;
    
    /**
     * @Column(type="boolean")
     */
    protected $is_unique = 0;
}
