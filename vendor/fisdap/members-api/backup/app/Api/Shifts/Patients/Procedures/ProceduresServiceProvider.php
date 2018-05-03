<?php
namespace Fisdap\Api\Shifts\Patients\Procedures;

use Fisdap\Api\Shifts\Patients\Procedures\Http\CardiacProceduresController;
use Fisdap\Api\Shifts\Patients\Procedures\Http\ProceduresController;
use Fisdap\Api\Shifts\Patients\Procedures\Http\IvProceduresController;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;

/**
 * Class ProceduresServiceProvider
 *
 * @package Fisdap\Api\Shifts\Patients\Procedures
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class ProceduresServiceProvider extends ServiceProvider
{
    public function boot(Router $router)
    {
        parent::boot($router);
    }

    public function map(Router $router)
    {
        // Procedures
        $router->group(
            [
                'prefix' => 'patients/procedures',
            ],
            function (Router $router) {
                $router->get('airways', [
                    'as'   => 'patients.procedures.airways',
                    'uses' => ProceduresController::class . '@getAirwayProcedures'
                ]);
                $router->get('cardiac', [
                    'as'   => 'patients.procedures.cardiac',
                    'uses' => CardiacProceduresController::class . '@getCardiacProcedures'
                ]);
                $router->get('cardiac/ectopies', [
                    'as'   => 'patients.procedures.cardiac.ectopies',
                    'uses' => CardiacProceduresController::class . '@getCardiacEctopies'
                ]);
                $router->get('cardiac/pacing-methods', [
                    'as'   => 'patients.procedures.cardiac.pacing-methods',
                    'uses' => CardiacProceduresController::class . '@getCardiacPacingMethods'
                ]);
                $router->get('cardiac/procedure-methods', [
                    'as'   => 'patients.procedures.cardiac.procedure-methods',
                    'uses' => CardiacProceduresController::class . '@getCardiacProcedureMethods'
                ]);
                $router->get('cardiac/rhythm-types', [
                    'as'   => 'patients.procedures.cardiac.rhythm-types',
                    'uses' => CardiacProceduresController::class . '@getCardiacRhythmTypes'
                ]);
                $router->get('ivs', [
                    'as'   => 'patients.procedures.ivs',
                    'uses' => ProceduresController::class . '@getIvProcedures'
                ]);
                $router->get('ivs/fluids', [
                    'as'   => 'patients.procedures.ivs.fluids',
                    'uses' => IvProceduresController::class . '@getIvFluids'
                ]);
                $router->get('ivs/sites', [
                    'as'   => 'patients.procedures.ivs.sites',
                    'uses' => IvProceduresController::class . '@getIvSites'
                ]);
                $router->get('other', [
                    'as'   => 'patients.procedures.other',
                    'uses' => ProceduresController::class . '@getOtherProcedures'
                ]);
            }
        );
    }
}
