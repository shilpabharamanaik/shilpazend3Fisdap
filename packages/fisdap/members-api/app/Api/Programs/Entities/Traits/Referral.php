<?php namespace Fisdap\Api\Programs\Entities\Traits;

use Doctrine\ORM\Mapping\Column;


/**
 * Class Referral
 *
 * @package Fisdap\Api\Programs\Entities\Traits
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait Referral
{
    /**
     * @var string|null
     * @Column(type="string", nullable=true)
     */
    protected $referral = null;

    /**
     * @var string|null
     * @Column(type="text", nullable=true)
     */
    protected $ref_description = null;


    /**
     * @return mixed
     */
    public function getReferral()
    {
        return $this->referral;
    }


    /**
     * @param mixed $referral
     */
    public function setReferral($referral)
    {
        $this->referral = $referral;
    }


    /**
     * @return mixed
     */
    public function getRefDescription()
    {
        return $this->ref_description;
    }


    /**
     * @param mixed $ref_description
     */
    public function setRefDescription($ref_description)
    {
        $this->ref_description = $ref_description;
    }
}
