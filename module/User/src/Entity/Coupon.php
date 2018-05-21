<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;

/**
 * Coupon for account ordering
 *
 * @Entity(repositoryClass="Fisdap\Data\Coupon\DoctrineCouponRepository")
 * @Table(name="fisdap2_coupons")
 * @HasLifecycleCallbacks
 */
class Coupon extends EntityBaseClass
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
     * @Column(type="string", unique=true)
     */
    protected $code;
    
    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $description;
    
    /**
     * @var integer
     * @Column(type="integer")
     */
    protected $configuration = 0;
    
    /**
     * @var integer
     * @Column(type="decimal", scale=4, precision=7)
     */
    protected $discount_percent = 0;
    
    /**
     * @var \DateTime
     * @Column(type="date")
     */
    protected $start_date;
    
    /**
     * @var \DateTime
     * @Column(type="date")
     */
    protected $end_date;
    
    /**
     * Determine if a coupon is valid
     * @return boolean
     */
    public function isValid()
    {
        $today = new \DateTime();
        $endDate = $this->end_date;
        $endDate->setTime(23, 59, 59);
        return ($today >= $this->start_date && $today <= $endDate);
    }
    
    public function getDiscountedPrice($originalPrice)
    {
        return round(($originalPrice - ($originalPrice * $this->discount_percent * .01)), 2, PHP_ROUND_HALF_DOWN);
    }
    
    /**
     * Given a coupon code, determine if it's valid
     *
     * @param string $code
     * @return mixed boolean | \Fisdap\Entity\Coupon
     */
    public static function isValidCoupon($code)
    {
        //Grab coupon from DB
        $coupon = self::getByCode($code);
        
        //If the coupon doesn't exist, return false
        if (!$coupon->id) {
            return false;
        }
        
        //If the coupon is valid, return the entity
        if ($coupon->isValid()) {
            return $coupon;
        }
        
        return false;
    }
    
    /**
     * Get a coupon entity given its code
     * @param string $code
     * @return \Fisdap\Entity\Coupon
     */
    public static function getByCode($code)
    {
        return EntityUtils::getRepository('Coupon')->findOneByCode($code);
    }
}
