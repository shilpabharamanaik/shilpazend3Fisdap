<?php namespace Fisdap\Api\Programs\Entities\Traits;

use Doctrine\ORM\Mapping\Column;


/**
 * Class ShiftDeadlines
 *
 * @package Fisdap\Api\Programs\Entities\Traits
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait ShiftDeadlines
{
    /**
     * @var int
     * @Column(name="BigBroInc", type="integer")
     */
    protected $late_field_deadline = 72;

    /**
     * @var int
     * @Column(name="ClinicalBigBroInc", type="integer")
     */
    protected $late_clinical_deadline = 72;

    /**
     * @var int
     * @Column(name="LabBigBroInc", type="integer")
     */
    protected $late_lab_deadline = 72;


    /**
     * @return mixed
     */
    public function getLateFieldDeadline()
    {
        return $this->late_field_deadline;
    }


    /**
     * @param mixed $late_field_deadline
     */
    public function setLateFieldDeadline($late_field_deadline)
    {
        $this->late_field_deadline = $late_field_deadline;
    }


    /**
     * @return mixed
     */
    public function getLateClinicalDeadline()
    {
        return $this->late_clinical_deadline;
    }


    /**
     * @param mixed $late_clinical_deadline
     */
    public function setLateClinicalDeadline($late_clinical_deadline)
    {
        $this->late_clinical_deadline = $late_clinical_deadline;
    }


    /**
     * @return mixed
     */
    public function getLateLabDeadline()
    {
        return $this->late_lab_deadline;
    }


    /**
     * @param mixed $late_lab_deadline
     */
    public function setLateLabDeadline($late_lab_deadline)
    {
        $this->late_lab_deadline = $late_lab_deadline;
    }
}
