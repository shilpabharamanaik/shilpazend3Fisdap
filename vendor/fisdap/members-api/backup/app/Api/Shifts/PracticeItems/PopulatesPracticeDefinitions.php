<?php namespace Fisdap\Api\Shifts\PracticeItems;

use Fisdap\Entity\ProgramLegacy;


/**
 * Contract for a PracticeItem Populator
 *
 * @package Fisdap\Api\Shifts\PracticeItems
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface PopulatesPracticeDefinitions
{
    /**
     * @param ProgramLegacy $program
     */
    public function populatePracticeDefinitions(ProgramLegacy $program);


    /**
     * @param ProgramLegacy $program
     */
    public function updatePPCPSkillsheets(ProgramLegacy $program);


    /**
     * @param ProgramLegacy $program
     */
    public function updateSkillsheets(ProgramLegacy $program);
}