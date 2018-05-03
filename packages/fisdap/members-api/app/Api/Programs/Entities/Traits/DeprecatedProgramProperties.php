<?php namespace Fisdap\Api\Programs\Entities\Traits;

use Doctrine\ORM\Mapping\Column;


/**
 * Class DeprecatedProgramProperties
 *
 * @package Fisdap\Api\Programs\Entities\Traits
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 * @deprecated
 */
trait DeprecatedProgramProperties
{
    /**
     * @Column(name="ProgramType", type="integer")
     * @deprecated
     */
    protected $program_type = 1;
    
    /**
     * @Column(name="ClassStartDates", type="string", nullable=true)
     * @deprecated
     */
    protected $class_start_dates;

    /**
     * @Column(name="TradesCode", type="integer")
     * @deprecated
     */
    protected $field_trade_code = 0;

    /**
     * @Column(name="DropsCode", type="integer")
     * @deprecated
     */
    protected $field_drop_code = 0;
    
    /**
     * @Column(name="Scheduler", type="boolean")
     * @deprecated
     */
    protected $use_scheduler = 1;

    /**
     * @Column(name="ClinicalTradesCode", type="integer")
     * @deprecated
     */
    protected $clinical_trade_code = 0;

    /**
     * @Column(name="ClinicalDropsCode", type="integer")
     * @deprecated
     */
    protected $clinical_drop_code = 0;

    /**
     * @Column(name="LabTradesCode", type="integer")
     * @deprecated
     */
    protected $lab_trade_code = 0;

    /**
     * @Column(name="LabDropsCode", type="integer")
     * @deprecated
     */
    protected $lab_drop_code = 0;

    /**
     * @Column(name="Requirements", type="integer")
     * @deprecated
     */
    protected $requirements = 0;

    /**
     * @Column(name="UseBeta", type="boolean")
     * @deprecated
     */
    protected $use_beta = 1;

    /**
     * @Column(name="scheduler_beta", type="boolean")
     * @deprecated
     */
    protected $scheduler_beta = 1;

    
    /**
     * @return int
     * @deprecated
     */
    public function get_use_scheduler() {
        return $this->use_scheduler;
    }


    /**
     * @return string
     * @deprecated 
     */
    public function getSchedulerUrl()
    {
        if ($this->scheduler_beta) {
            return "/scheduler";
        } else {
            return \Util_GetLegacyTopNavLinks::getLink(\Util_GetLegacyTopNavLinks::SCHEDULER);
        }
    }
}