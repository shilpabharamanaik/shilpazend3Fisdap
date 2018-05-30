<?php namespace Fisdap\Api\Programs\Listeners\ProgramSettings\Pipes;

use Fisdap\Api\Programs\Listeners\ProgramSettings\EstablishesProgramSettings;

/**
 * Class StudentPermissionsSetter
 *
 * @package Fisdap\Api\Programs\Listeners\ProgramSettings\Pipes
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class StudentPermissionsSetter
{
    /**
     * @param EstablishesProgramSettings $listener
     */
    public function set(EstablishesProgramSettings $listener)
    {
        $listener->getProgram()->set_can_students_create_field(
            $listener->getEvent()->getSettings()->studentPermissions->createField
        );
        $listener->getProgram()->set_can_students_create_clinical(
            $listener->getEvent()->getSettings()->studentPermissions->createClinical
        );
        $listener->getProgram()->set_can_students_create_lab(
            $listener->getEvent()->getSettings()->studentPermissions->createLab
        );

        $listener->getProgram()->setIncludeNarrative(
            $listener->getEvent()->getSettings()->studentPermissions->includeNarrative
        );

        $listener->getProgram()->getProgramSettings()->setAllowTardy(
            $listener->getEvent()->getSettings()->allowTardy
        );
        
        // todo - reconcile these duplications
        $listener->getProgram()->setStudentViewFullCalendar(
            $listener->getEvent()->getSettings()->studentPermissions->viewFullCalendar
        );
        $listener->getProgram()->getProgramSettings()->setStudentViewFullCalendar(
            $listener->getEvent()->getSettings()->studentPermissions->viewFullCalendar
        );

        $listener->getProgram()->setCanStudentsPickField(
            $listener->getEvent()->getSettings()->studentPermissions->pickField
        );
        $listener->getProgram()->getProgramSettings()->setStudentPickField(
            $listener->getEvent()->getSettings()->studentPermissions->pickField
        );

        $listener->getProgram()->setCanStudentsPickClinical(
            $listener->getEvent()->getSettings()->studentPermissions->pickClinical
        );
        $listener->getProgram()->getProgramSettings()->setStudentPickClinical(
            $listener->getEvent()->getSettings()->studentPermissions->pickClinical
        );

        $listener->getProgram()->setCanStudentsPickLab(
            $listener->getEvent()->getSettings()->studentPermissions->pickLab
        );
        $listener->getProgram()->getProgramSettings()->setStudentPickLab(
            $listener->getEvent()->getSettings()->studentPermissions->pickLab
        );

        $listener->getProgram()->setAllowAbsentWithPermission(
            $listener->getEvent()->getSettings()->studentPermissions->allowAbsentWithPermission
        );
        $listener->getProgram()->getProgramSettings()->setAllowAbsent(
            $listener->getEvent()->getSettings()->allowAbsent
        );

        $listener->getProgram()->getProgramSettings()->setStudentSwitchField(
            $listener->getEvent()->getSettings()->studentPermissions->switchField
        );
        $listener->getProgram()->getProgramSettings()->setSwitchFieldNeedsPermission(
            $listener->getEvent()->getSettings()->studentPermissions->switchFieldNeedsPermission
        );

        $listener->getProgram()->getProgramSettings()->setStudentSwitchClinical(
            $listener->getEvent()->getSettings()->studentPermissions->switchClinical
        );
        $listener->getProgram()->getProgramSettings()->setSwitchClinicalNeedsPermission(
            $listener->getEvent()->getSettings()->studentPermissions->switchClinicalNeedsPermission
        );

        $listener->getProgram()->getProgramSettings()->setStudentSwitchLab(
            $listener->getEvent()->getSettings()->studentPermissions->switchLab
        );
        $listener->getProgram()->getProgramSettings()->setSwitchLabNeedsPermission(
            $listener->getEvent()->getSettings()->studentPermissions->switchLabNeedsPermission
        );
    }
}
