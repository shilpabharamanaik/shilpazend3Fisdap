<?php namespace Fisdap\Api\Shifts\Patients\Skills;

use Fisdap\Api\Shifts\Patients\Skills\Http\MedicationsController;
use Fisdap\Api\Shifts\Patients\Skills\Http\SkillsController;
use Fisdap\Api\Shifts\Patients\Skills\Http\VitalsController;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;

/**
 * Class SkillsServiceProvider
 * @package Fisdap\Api\Shifts\Patients\Skills
 * @author  Isaac White <isaac.white@ascendlearning.com>
 */
final class SkillsServiceProvider extends ServiceProvider
{
    /**
     * @param Router $router
     */
    public function boot(Router $router)
    {
        parent::boot($router);
    }

    /**
     * @param Router $router
     */
    public function map(Router $router)
    {
        // Patient Skills
        $router->group([
            'prefix' => 'patients/{patientId}/skills',
        ],  function (Router $router) {
            $router->put('airways/{airwayId}', [
                'as'   => 'patients.patientId.skills.airways.airwayId',
                'uses' => SkillsController::class . '@setPatientAirways'
            ]);
            $router->put('cardiacs/{cardiacId}', [
                'as'   => 'patients.patientId.skills.cardiacs.cardiacId',
                'uses' => SkillsController::class . '@setPatientCardiacs'
            ]);
            $router->put('ivs/{ivId}', [
                'as'   => 'patients.patientId.skills.ivs.ivId',
                'uses' => SkillsController::class . '@setPatientIvs'
            ]);
            $router->put('medications/{medicationId}', [
                'as'   => 'patients.patientId.skills.medications.medicationId',
                'uses' => SkillsController::class . '@setPatientMedications'
            ]);
            $router->put('others/{otherInterventionId}', [
                'as'   => 'patients.patientId.skills.others.otherInterventionId',
                'uses' => SkillsController::class . '@setPatientOthers'
            ]);
            $router->put('vitals/{vitalId}', [
                'as'   => 'patients.patientId.skills.vitals.vitalId',
                'uses' => SkillsController::class . '@setPatientVitals'
            ]);
        });

        // Shift (quick add) Skills
        $router->group([
            'prefix' => 'shifts/{shiftId}/skills',
        ],  function (Router $router) {
            $router->put('airways/{airwayId}', [
                'as'   => 'shifts.shiftId.skills.airways.airwayId',
                'uses' => SkillsController::class . '@setShiftAirways'
            ]);
            $router->put('cardiacs/{cardiacId}', [
                'as'   => 'shifts.shiftId.skills.cardiacs.cardiacId',
                'uses' => SkillsController::class . '@setShiftCardiacs'
            ]);
            $router->put('ivs/{ivId}', [
                'as'   => 'shifts.shiftId.skills.ivs.ivId',
                'uses' => SkillsController::class . '@setShiftIvs'
            ]);
            $router->put('medications/{medicationId}', [
                'as'   => 'shifts.shiftId.skills.medications.medicationId',
                'uses' => SkillsController::class . '@setShiftMedications'
            ]);
            $router->put('others/{otherInterventionId}', [
                'as'   => 'shifts.shiftId.skills.others.otherInterventionId',
                'uses' => SkillsController::class . '@setShiftOthers'
            ]);
            $router->put('vitals/{vitalId}', [
                'as'   => 'shifts.shiftId.skills.vitals.vitalId',
                'uses' => SkillsController::class . '@setShiftVitals'
            ]);
        });
        // Medications
        $router->group([
            'prefix' => 'patients/skills/medications',
        ],  function (Router $router) {
                $router->get('types', [
                    'as'   => 'patients.skills.medications.types',
                    'uses' => MedicationsController::class . '@getMedicationTypes'
                ]);
                $router->get('routes', [
                    'as'   => 'patients.skills.medications.routes',
                    'uses' => MedicationsController::class . '@getMedicationRoutes'
                ]);
            }
        );
        
        // Vitals
        $router->group([
            'prefix' => 'patients/skills/vitals',
        ],  function (Router $router) {
                $router->get('pulse-qualities', [
                    'as'   => 'patients.skills.vitals.pulse_qualities',
                    'uses' => VitalsController::class . '@getVitalsPulseQualities'
                ]);
                $router->get('respiratory-qualities', [
                    'as'   => 'patients.skills.vitals.respiratory_qualities',
                    'uses' => VitalsController::class . '@getVitalsRespiratoryQualities'
                ]);
                $router->get('skin-conditions', [
                    'as'   => 'patients.skills.vitals.skin_conditions',
                    'uses' => VitalsController::class . '@getVitalsSkinConditions'
                ]);
                $router->get('lung-sounds', [
                    'as'   => 'patients.skills.vitals.lung_sounds',
                    'uses' => VitalsController::class . '@getVitalsLungSounds'
                ]);
            }
        );

        $router->delete('skills/airways/{airwayId}', [
            'as'   => 'skills.airways.airwayId',
            'uses' => SkillsController::class . '@deleteAirway'
        ]);
        $router->delete('skills/cardiacs/{cardiacInterventionId}', [
            'as'   => 'skills.cardiacs.cardiacInterventionId',
            'uses' => SkillsController::class . '@deleteCardiac'
        ]);
        $router->delete('skills/ivs/{ivId}', [
            'as'   => 'skills.ivs.ivId',
            'uses' => SkillsController::class . '@deleteIv'
        ]);
        $router->delete('skills/medications/{medicationId}', [
            'as'   => 'skills.medications.medicationId',
            'uses' => SkillsController::class . '@deleteMed'
        ]);
        $router->delete('skills/others/{otherInterventionId}', [
            'as'   => 'skills.others.otherInterventionId',
            'uses' => SkillsController::class . '@deleteOther'
        ]);
        $router->delete('skills/vitals/{vitalId}', [
            'as'   => 'skills.vitals.vitalId',
            'uses' => SkillsController::class . '@deleteVital'
        ]);
    }
}

