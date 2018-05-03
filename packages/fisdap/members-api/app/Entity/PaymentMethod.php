<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;


/**
 * Payment Method
 * 
 * @Entity
 * @Table(name="fisdap2_payment_method")
 */
class PaymentMethod extends Enumerated
{
	/**
	 * @var string
	 * @Column(type="string", nullable=true)
	 */
	protected $description;
}