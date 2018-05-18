<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;


/**
 * Legacy Entity class for Assets.
 * 
 * @Entity(repositoryClass="Fisdap\Data\Asset\DoctrineAssetLegacyRepository")
 * @Table(name="Asset_def")
 */
class AssetLegacy extends EntityBaseClass
{
	/**
	 * @Id
	 * @Column(name="AssetDef_id", type="integer")
	 * @GeneratedValue
	 */
	protected $id;
	
	/**
	 * @Column(name="AssetName", type="string")
	 */
	protected $name;
	
	/**
	 * @Column(name="AssetDescription", type="string")
	 */
	protected $description;
	
	/**
	 * @Column(name="Approved", type="boolean")
	 */
	protected $approved = 0;
	
	/**
	 * @Column(name="DateApproved", type="datetime")
	 */
	protected $date_approved;
	
	/**
	 * @Column(name="DataType_id", type="integer")
	 */
	protected $data_type_id;
	
	/**
	 * @Column(name="Data_id", type="integer")
	 */
	protected $data_id;
	
	/**
	 * @Column(name="Tree_id", type="string")
	 */
	protected $tree_id;
	
	/**
	 * @Column(name="Parent_id", type="integer")
	 */
	protected $parent_id;
	
	/**
	 * @Column(name="ReferenceType_id", type="integer")
	 */
	protected $reference_type_id;
	
	/**
	 * @Column(name="ReferenceData_id", type="integer")
	 */
	protected $reference_data_id;
	
	/**
	 * @Column(name="DatePublished", type="datetime")
	 */
	protected $date_published;
	
	/**
	 * @Column(name="SiblingOrder", type="integer")
	 */
	protected $sibling_order;
	
	/**
	 * @Column(name="PaidFlag", type="boolean")
	 */
	protected $paid_flag;
	
	/**
	 * @Column(name="StaffRating", type="integer")
	 */
	protected $staff_rating;
	
	/**
	 * @Column(name="PointEligibility", type="integer")
	 */
	protected $point_eligibility;
	
	/**
	 * @Column(name="DevelopmentStatus", type="integer")
	 */
	protected $development_status;
	
	public function init()
    {
    }
}
