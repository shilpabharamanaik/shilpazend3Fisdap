<?php namespace Fisdap\Api\Programs\Entities\Traits;

use Doctrine\ORM\Mapping\Column;


/**
 * Class Phones
 *
 * @package Fisdap\Api\Programs\Entities\Traits
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait Phones
{
    /**
     * @var string
     * @Column(name="ProgramPhone", type="string", nullable=true)
     */
    protected $phone = null;

    /**
     * @var string
     * @Column(name="ProgramFax", type="string", nullable=true)
     */
    protected $fax = null;


    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }


    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }


    /**
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }


    /**
     * @param string $fax
     */
    public function setFax($fax)
    {
        $this->fax = $fax;
    }
}
