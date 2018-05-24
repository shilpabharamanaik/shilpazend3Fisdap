<?php namespace Fisdap\Api\Programs\Entities\Traits;

use Doctrine\ORM\Mapping\Column;


/**
 * Class Accreditation
 *
 * @package Fisdap\Api\Programs\Entities\Traits
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait Accreditation
{
    /**
     * @var bool|null
     * @Column(name="Accredited", type="integer", nullable=true)
     */
    protected $accredited = null;

    /**
     * @var int|null the CoAEMSP number that identifies this program
     * @Column(name="coaemsp_program_id", type="integer", nullable=true)
     */
    protected $coaemsp_program_id = null;

    /**
     * @var int|null the year that this program was first accredited
     * @Column(name="year_accredited", type="integer", nullable=true)
     */
    protected $year_accredited = null;


    /**
     * @return bool|null
     */
    public function getAccredited()
    {
        return $this->accredited;
    }


    /**
     * @param bool $accredited
     */
    public function setAccredited($accredited)
    {
        $this->accredited = $accredited;
    }


    /**
     * @return int
     */
    public function getCoaemspProgramId()
    {
        return $this->coaemsp_program_id;
    }


    /**
     * @param int $coaemsp_program_id
     */
    public function setCoaemspProgramId($coaemsp_program_id)
    {
        $this->coaemsp_program_id = $coaemsp_program_id;
    }


    /**
     * @return int
     */
    public function getYearAccredited()
    {
        return $this->year_accredited;
    }


    /**
     * @param int $year_accredited
     */
    public function setYearAccredited($year_accredited)
    {
        $this->year_accredited = $year_accredited;
    }
}
