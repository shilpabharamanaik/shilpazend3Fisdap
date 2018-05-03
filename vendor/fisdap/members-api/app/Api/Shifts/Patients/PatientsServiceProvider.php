<?php


namespace Fisdap\Api\Shifts\Patients;

use Fisdap\Api\Shifts\Patients\Http\CardiacController;
use Fisdap\Api\Shifts\Patients\Http\EnumerationsController;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;

/**
 * Class PatientServiceProvider
 * @package Fisdap\Api\Shifts\Patients
 * @author  Isaac White <iwhite@fisdap.net>
 */
class PatientsServiceProvider extends ServiceProvider
{
    /**
     * @param Router $router
     */
    public function boot()
    {
		$router = app('router'); // Router Instance
        parent::boot();
    }

    /**
     * @param Router $router
     */
    public function map(Router $router)
    {
        // Cardiac Endpoints
        $router->group([
                'prefix' => 'patients/cardiac/'
            ],
            function(Router $router) {
                $router->get('witness-statuses', [
                        'as'   => 'patients.cardiac.witness-statuses',
                        'uses' => CardiacController::class . '@getWitnessStatuses'
                    ]
                );
                $router->get('pulse-returns', [
                        'as'   => 'patients.cardiac.pulse-returns',
                        'uses' => CardiacController::class . '@getPulseReturns'
                    ]
                );
            }
        );

        // Non-specific Endpoints (Enumerations)
        $router->group([
                'prefix' => 'patients'
            ],
            function(Router $router) {
                $router->get('ambulance-response-modes', [
                        'as'   => 'patients.ambulance-response-mode',
                        'uses' => EnumerationsController::class . '@getAmbulanceResponseModes'
                    ]
                );
                $router->get('impressions', [
                        'as'   => 'patients.impressions',
                        'uses' => EnumerationsController::class . '@getImpressions'
                    ]
                );
                $router->get('complaints', [
                        'as'   => 'patients.complaints',
                        'uses' => EnumerationsController::class . '@getComplaints'
                    ]
                );
                $router->get('subjects', [
                        'as'   => 'patients.subjects',
                        'uses' => EnumerationsController::class . '@getSubjects'
                    ]
                );
                $router->get('criticalities', [
                        'as'   => 'patients.criticalities',
                        'uses' => EnumerationsController::class . '@getPatientsCriticalities'
                    ]
                );
                $router->get('dispositions', [
                        'as'   => 'patients.dispositions',
                        'uses' => EnumerationsController::class . '@getDispositions'
                    ]
                );
                $router->get('airway-management-sources', [
                        'as'   => 'patients.airway-management-sources',
                        'uses' => EnumerationsController::class . '@getAirwayManagementSources'
                    ]
                );
                $router->get('mental-alertness', [
                        'as'   => 'patients.mental-alertness',
                        'uses' => EnumerationsController::class . '@getMentalAlertness'
                    ]
                );
                $router->get('mental-orientations', [
                        'as'   => 'patients.mental-orientations',
                        'uses' => EnumerationsController::class . '@getMentalOrientations'
                    ]
                );
            }
        );
    }
}






