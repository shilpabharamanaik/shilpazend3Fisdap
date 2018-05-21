<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for Guided Tour steps.
 *
 * @Entity
 * @Table(name="fisdap2_guided_tour_steps")
 * @HasLifecycleCallbacks
 */
class GuidedTourStep extends EntityBaseClass
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @var \Fisdap\Entity\Slot
     * @ManyToOne(targetEntity="GuidedTour", inversedBy="steps")
     */
    protected $guided_tour;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $focus_element;

    /**
     * @Column(type="text")
     */
    protected $step_text = "";
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $pointer;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $auto_xy_pos;
    
    /**
     * @Column(type="integer", nullable=true)
     */
    protected $manual_x_pos;
    
    /**
     * @Column(type="integer", nullable=true)
     */
    protected $manual_y_pos;
    
    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $hidden_on_page_load;
    
    
    public function init()
    {
    }
}
