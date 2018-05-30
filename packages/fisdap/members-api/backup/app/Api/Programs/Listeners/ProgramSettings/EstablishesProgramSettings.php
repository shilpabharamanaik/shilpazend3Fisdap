<?php namespace Fisdap\Api\Programs\Listeners\ProgramSettings;

use Fisdap\Api\Programs\Events\ProgramWasCreated;
use Fisdap\Entity\ProgramLegacy;

/**
 * Event listener contract for establishing program settings when a program (ProgramLegacy Entity) was created
 *
 * @package Fisdap\Api\Programs\Listeners
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface EstablishesProgramSettings
{
    /**
     * @param ProgramWasCreated $event
     */
    public function handle(ProgramWasCreated $event);


    /**
     * @return ProgramLegacy
     */
    public function getProgram();


    /**
     * @return ProgramWasCreated
     */
    public function getEvent();
}
