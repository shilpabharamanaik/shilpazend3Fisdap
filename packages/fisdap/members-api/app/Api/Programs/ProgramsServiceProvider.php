<?php namespace Fisdap\Api\Programs;

use Fisdap\Api\Programs\Finder\FindsPrograms;
use Fisdap\Api\Programs\Finder\ProgramsFinder;
use Fisdap\Api\Programs\GoalSets\Finder\FindsGoalSets;
use Fisdap\Api\Programs\GoalSets\Finder\GoalSetsFinder;
use Fisdap\Api\Programs\GoalSets\Http\GoalSetsController;
use Fisdap\Api\Programs\Http\ProgramsController;
use Fisdap\Api\Programs\Settings\ProgramSettingsController;
use Fisdap\Api\Programs\Sites\Preceptors\Http\PreceptorsController;
use Fisdap\Api\Programs\Types\Http\ProgramTypesController;
use Fisdap\Api\Programs\Sites\Bases\BasesController;
use Fisdap\Api\Programs\Sites\Bases\Finder\BasesFinder;
use Fisdap\Api\Programs\Sites\Bases\Finder\FindsBases;
use Fisdap\Api\Programs\Sites\Finder\FindsSites;
use Fisdap\Api\Programs\Sites\Finder\SitesFinder;
use Fisdap\Api\Programs\Sites\SitesController;
use Fisdap\Api\Reports\Finder\FindsReports;
use Fisdap\Api\Reports\Finder\ReportsFinder;
use Fisdap\Api\Reports\Http\ReportsController;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

/**
 * Enables program-related routes, providing REST API endpoint documentation for each, including sites and bases
 *
 * @package Fisdap\Api\Programs
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @author  Nick Karnick <nkarnick@fisdap.net>
 * @codeCoverageIgnore
 * @todo remaining middleware
 */
final class ProgramsServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function boot()
    {
		$router = app('router'); // Router Instance
        parent::boot();
    }


    /**
     * Put program-related routes here
     *
     * ALL NON-RESOURCE CONTROLLERS SHOULD BE NAMED (in order to work properly with New Relic)
     * see http://laravel.com/docs/routing#named-routes
     *
     * @param Router $router
     */
    public function map(Router $router)
    {
        $router->get('programs/types', [
            'as' => 'programs.types.index',
            'uses' => ProgramTypesController::class . '@index'
        ]);

        $router->get('programs/{programId}', [
            'middleware' => [
                'userContextProgramIdMatchesRouteId'
            ],
            'as' => 'programs.show',
            'uses' => ProgramsController::class . '@show'
        ]);

        $router->get('programs/{programId}/narrative-definitions', [
            'middleware' => [
                'userContextProgramIdMatchesRouteId'
            ],
            'as' => 'programs.narrative-definitions',
            'uses' => ProgramSettingsController::class . '@getNarrativeDefinitions'
        ]);


        // Settings
        $router->group(
            [
                'prefix' => 'programs/{programId}/settings',
                'middleware' => 'userContextProgramIdMatchesRouteId'
            ],
            function (Router $router) {
                $router->patch('/', [
                    'as' => 'programs.programId.settings',
                    'uses' => ProgramSettingsController::class . '@updateProgramSettings'
                ]);
            }
        );


        // Goal Sets
        $router->group(
            [
                'prefix' => 'programs/{programId}/goal-sets',
                'middleware' => 'userContextProgramIdMatchesRouteId'
            ],
            function (Router $router) {
                $router->get('/', [
                    'as' => 'programs.programId.goal-sets',
                    'uses' => GoalSetsController::class . '@show'
                ]);
            }
        );

        // Reports
        $router->group(
            [
                'prefix' => 'programs/{programId}/reports',
                'middleware' => [
                    'userHasReportAccess',
                    'userContextProgramIdMatchesRouteId',
                    'goalSetProgramMismatch',
                    'userCanViewStudents'
                ]
            ],
            function (Router $router) {
                $router->get('/3c2', [
                    'as' => 'programs.programId.reports.3c2',
                    'uses' => ReportsController::class . '@run3c2Report'
                ]);
            }
        );

        $router->post('/programs', [
            'middleware' => [
                'userMustBeStaff'
            ],
            'as' => 'programs.store',
            'uses' => ProgramsController::class . '@store',
        ]);

        $router->get('programs/sites/{id}', SitesController::class . '@show');

        $router->get('programs/{programId}/sites', [
            'middleware' => [
                'userContextProgramIdMatchesRouteId'
            ],
            'as' => 'programs.sites',
            'uses' => SitesController::class . '@getProgramSites'
        ]);

        $router->get('programs/sites/bases/{id}', BasesController::class . '@show');

        // Preceptor Endpoints
        $router->group(
            ['prefix' => 'sites'],
            function (Router $router) {
                $router->group(
                    ['prefix' => '{siteId}/preceptors'],
                    function (Router $router) {
                        $router->get('/', [
                            'as' => 'sites.siteId.preceptors',
                            'uses' => PreceptorsController::class . '@getPreceptorsBySite'
                        ]);

                        $router->post('/', [
                            'as' => 'sites.siteId.preceptors',
                            'uses' => PreceptorsController::class . '@store'
                        ]);
                    }
                );

                $router->get('preceptors/{preceptorId}', [
                    'as' => 'sites.preceptors.preceptorId',
                    'uses' => PreceptorsController::class . '@show'
                ]);
            }
        );
    }


    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->app->singleton(FindsPrograms::class, ProgramsFinder::class);
        $this->app->singleton(FindsSites::class, SitesFinder::class);
        $this->app->singleton(FindsBases::class, BasesFinder::class);
        $this->app->singleton(FindsGoalSets::class, GoalSetsFinder::class);
        $this->app->singleton(FindsReports::class, ReportsFinder::class);
    }
}
