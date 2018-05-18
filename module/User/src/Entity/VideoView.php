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
 * Entity class for signoff emails.
 * 
 * @Entity
 * @Table(name="fisdap2_video_views")
 * @HasLifecycleCallbacks
 */
class VideoView extends EntityBaseClass
{
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;

	/**
	 * @Column(type="text")
	 */
	protected $video_key;

	/**
	 * @Column(type="datetime", nullable=true)
	 */
	protected $view_time;
	
	/**
     * @ManyToOne(targetEntity="User", cascade={"persist"})
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
	protected $user;
}
