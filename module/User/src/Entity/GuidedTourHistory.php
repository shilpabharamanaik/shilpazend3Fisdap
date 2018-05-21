<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for Guided Tour History -- a user gets  record in here once they have either completed a tour or chosen 'I'm good'
 *
 * @Entity
 * @Table(name="fisdap2_guided_tour_history")
 * @HasLifecycleCallbacks
 */
class GuidedTourHistory extends EntityBaseClass
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @ManyToOne(targetEntity="GuidedTour")
    */
    protected $guided_tour;
    
    /**
     * @ManyToOne(targetEntity="UserContext")
     * @JoinColumn(name="user_role_id", referencedColumnName="id")
     */
    protected $user_context;
}
