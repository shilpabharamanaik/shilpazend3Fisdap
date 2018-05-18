<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;


/**
 * Entity class for product quickbooks information
 * 
 * @Entity
 * @Table(name="fisdap2_product_quickbooks_info")
 */
class ProductQuickbooksInfo extends EntityBaseClass
{
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;

	/**
	 * @var \Fisdap\Entity\Product
	 * @OneToOne(targetEntity="Product", inversedBy="quickbooks_info")
	 */
	protected $product;
	
	/**
	 * @var string
	 * @Column(type="string")
	 */
	protected $item_name;
	
	/**
	 * @var string
	 * @Column(type="string")
	 */
	protected $package_item_name;
	
	/**
	 * @var decimal
	 * @Column(type="decimal", precision=6, scale=2)
	 */
	protected $package_discount_price;
}