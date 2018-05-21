<?php namespace User\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;

/**
 * Entity class for Guided Tour.
 *
 * @Entity(repositoryClass="Fisdap\Data\GuidedTour\DoctrineGuidedTourRepository")
 * @Table(name="fisdap2_guided_tours")
 * @HasLifecycleCallbacks
 */
class GuidedTour extends EntityBaseClass
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $url;
    
    /**
     * @ManyToOne(targetEntity="Role")
     */
    protected $role;
    
    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $active;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $name;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $welcome_msg;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $end_msg;
    
    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="GuidedTourStep", mappedBy="guided_tour", cascade={"persist","remove"})
     */
    protected $steps;
    
    public function init()
    {
        $this->steps = new ArrayCollection;
    }
    
    public function userHasCompleted($userContextId)
    {
        $repo = EntityUtils::getRepository('GuidedTour');
        $history_record_id = $repo->getTourHistoryByUser($this->id, $userContextId);
        
        return ($history_record_id) ? true : false;
    }
}
