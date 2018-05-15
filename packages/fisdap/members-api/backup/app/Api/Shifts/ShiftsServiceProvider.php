<?php namespace Fisdap\Api\Shifts;

use Fisdap\Api\Programs\Sites\Bases\BasesController;
use Fisdap\Api\Programs\Sites\SitesController;
use Fisdap\Api\Shifts\Finder\FindsShifts;
use Fisdap\Api\Shifts\Finder\ShiftsFinder;
use Fisdap\Api\Shifts\Http\ShiftsController;
use Fisdap\Api\Shifts\Patients\Http\PatientsController;
use Fisdap\Api\Shifts\PracticeItems\PopulatesPracticeDefinitions;
use Fisdap\Api\Shifts\PracticeItems\PracticeItemsController;
use Fisdap\Api\Shifts\PracticeItems\PracticePopulator;
use Fisdap\Api\Shifts\PreceptorSignoffs\Http\PreceptorRatingsController;
use Fisdap\Api\Shifts\PreceptorSignoffs\Http\PreceptorSignoffsController;
use Fisdap\Api\Students\Http\StudentsController;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

/**
 * Enables shift-related routes, providing REST API endpoint documentation for each, and provides shift-related services
 *
 * @package Fisdap\Api\Shifts
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
final class ShiftsServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->app->singleton(FindsShifts::class, ShiftsFinder::class);
        $this->app->singleton(PopulatesPracticeDefinitions::class, PracticePopulator::class);
    }


    /**
     * @inheritdoc
     */
    public function boot(Router $router)
    {
        parent::boot($router);
    }


    /**
     * Put shift-related routes here
     *
     * ALL NON-RESOURCE CONTROLLERS SHOULD BE NAMED (in order to work properly with New Relic)
     * see http://laravel.com/docs/routing#named-routes
     *
     * @param Router $router
     */
    public function map(Router $router)
    {
        /*
         * Instructors
         */

        $router->get('instructors/{instructorId}/shifts', [
            'middleware' => [
                'mustHaveRole:instructor',
                'roleDataIdMatchesRouteId:instructor',
                'instructorCanViewAllData'
            ],
            'as'         => 'instructors.shifts',
            'uses'       => ShiftsController::class . '@getInstructorShifts'
        ]);


        /*
         * Practice Items
         */


        /**
         * Preceptor Rating Items
         */
        $router->group(
            [
            'prefix' => 'shifts'
        ],
            function (Router $router) {
                $router->get('preceptor-rating-types', [
                    'as'   => 'preceptor-rating-types',
                    'uses' => PreceptorRatingsController::class . '@getPreceptorRatingTypes'
                ]);
                $router->get('preceptor-rating-rater-types', [
                    'as'   => 'preceptor-rating-rater-types',
                    'uses' => PreceptorRatingsController::class . '@getPreceptorRatingRaterTypes'
                ]);
                // The following use the same endpoint. The reason for the duplication is for ease of organization.
                $router->get('patients/signoffs/{signoffId}', [
                    'as'   => 'shifts.patients.signoffs.signoffId',
                    'uses' => PreceptorSignoffsController::class . '@index'
                ]);
                $router->get('signoffs/{signoffId}', [
                    'as'   => 'shifts.signoffs.signoffId',
                    'uses' => PreceptorSignoffsController::class . '@index'
                ]);

                $router->group(
                    [
                    'prefix' => 'patients/{patientId}',
                    'middleware' => [
                        
                    ],
                ],
                    // The following are for already created patients since they do not need a
                    // shift Id to find them in the database.
                    function (Router $router) {
                        $router->get('/', [
                            'as'   => 'shifts.patients.patientId',
                            'uses' => PatientsController::class . '@getPatient'
                        ]);
                        $router->patch('/', [
                            'as'   => 'shifts.patients.patientId',
                            'uses' => PatientsController::class . '@updatePatient'
                        ]);
                        $router->delete('/', [
                            'as'   => 'shifts.patients.patientId',
                            'uses' => PatientsController::class . '@deletePatient'
                        ]);
                        $router->put('signoffs', [
                            'as'   => 'shifts.patients.patientId.signoffs',
                            'uses' => PreceptorSignoffsController::class . '@setPatientSignoff'
                        ]);
                    }
                );
            }
        );

        /*
         * Programs
         */

        
        $router->group([
            'prefix' => 'shifts/{shiftId}',
            'middleware' => [

            ],
        ], function (Router $router) {
            $router->get('/', [
                'middleware' => [
                    'instructorCanViewAllData',
                    'shiftStudentProgramMatchesUserContextProgram',
                ],
                'as'         => 'shift',
                'uses'       => ShiftsController::class . '@show',
            ]);
            $router->patch('/', [
                'as'   => 'shifts.shiftId',
                'uses' => ShiftsController::class . '@updateShift'
            ]);
            $router->put('signoffs', [
                'as'   => 'shifts.shiftId.signoffs',
                'uses' => PreceptorSignoffsController::class . '@setShiftSignoff'
            ]);


            $router->get('practice-items', [ // shifts/{shiftId}/practice-items
                'as' => 'shifts.practice-items',
                'uses' => PracticeItemsController::class . '@getPracticeItems'
            ]);
            $router->group(
                [
                'prefix' => 'patients',
            ],
                function (Router $router) {
                    $router->get('/', [
                        'as'   => 'shifts.shiftId.patients',
                        'uses' => PatientsController::class . '@getPatientsForShift'
                    ]);
                    $router->post('/', [
                        'as'   => 'shifts.shiftId.patients',
                        'uses' => PatientsController::class . '@createPatient'
                    ]);
                }
            );
        });


        $router->get('programs/{programId}/shifts', [
            'middleware' => [
                'instructorCanViewAllData',
                'userContextProgramIdMatchesRouteId',
                'studentHasSkillsTrackerOrScheduler',
            ],
            'as'         => 'programs.shifts',
            'uses'       => ShiftsController::class . '@getProgramShifts'
        ]);


        /*
         * Students
         */

        $router->group([
            'prefix'     => 'students/{studentId}',
            'middleware' => [
                'instructorCanViewAllData',
                'studentHasSkillsTrackerOrScheduler',
                'roleDataIdMatchesRouteId:student'
            ],
        ], function (Router $router) {
            $router->post('/shifts', [
                'as'   => 'students.studentId.shifts',
                'uses' => ShiftsController::class . '@createShift'
            ]);
            $router->get('/', [
                'as'   => '',
                'uses' => StudentsController::class . '@show'
            ]);
            $router->get('shifts', [
                'as'   => 'students.shifts',
                'uses' => ShiftsController::class . '@getStudentShifts'
            ]);
            
            $router->get('shifts/sites/distinct', [
                'as'   => 'students.shifts.sites.distinct',
                'uses' => SitesController::class . '@getDistinctStudentShiftSites'
            ]);
            
            $router->get('shifts/bases/distinct', [
                'as'   => 'students.shifts.bases.distinct',
                'uses' => BasesController::class . '@getDistinctStudentShiftBases'
            ]);
        });
    }
}
