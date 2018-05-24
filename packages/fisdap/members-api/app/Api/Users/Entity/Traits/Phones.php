<?php namespace Fisdap\Api\Users\Entity\Traits;

use Doctrine\ORM\Mapping\Column;


/**
 * Trait Phones
 *
 * @package Fisdap\Api\Users\Entity\Traits
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
trait Phones
{
    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $home_phone = "";

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $work_phone = "";

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $cell_phone = "";


    /**
     * @param string $value
     *
     * @return $this
     *
     * @deprecated
     */
    public function set_home_phone($value)
    {
        $this->home_phone = $value;
        if ($this->getCurrentRoleName() == "student") {
            $this->getCurrentRoleData()->home_phone = $value;
        }
        return $this;
    }


    /**
     * @param string $homePhone
     */
    public function setHomePhone($homePhone)
    {
        $this->home_phone = $homePhone;
    }


    /**
     * @return string
     */
    public function getHomePhone()
    {
        return $this->home_phone;
    }


    /**
     * @param string $value
     *
     * @return $this
     *
     * @deprecated
     */
    public function set_cell_phone($value)
    {
        $this->cell_phone = $value;
        $roleData = $this->getCurrentRoleData();
        if ($roleData) {
            $roleData->cell_phone = $value;
        }
        return $this;
    }


    /**
     * @param string $cellPhone
     */
    public function setCellPhone($cellPhone)
    {
        $this->cell_phone = $cellPhone;
    }


    /**
     * @return string
     */
    public function getCellPhone()
    {
        return $this->cell_phone;
    }


    /**
     * @param string $value
     *
     * @return $this
     *
     * @deprecated
     */
    public function set_work_phone($value)
    {
        $this->work_phone = $value;
        if ($this->getCurrentRoleName() == "student") {
            $this->getCurrentRoleData()->work_phone = $value;
        } else if ($this->getCurrentRoleName() == "instructor") {
            $this->getCurrentRoleData()->office_phone = $value;
        }
        return $this;
    }


    /**
     * @param string $workPhone
     */
    public function setWorkPhone($workPhone)
    {
        $this->work_phone = $workPhone;
    }


    /**
     * @return string
     */
    public function getWorkPhone()
    {
        return $this->work_phone;
    }
}