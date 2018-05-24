<?php namespace Fisdap\Api\Shifts\Signatures;

use Fisdap\Api\Shifts\Signatures\Http\SignaturesController;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;

final class SignatureServiceProvider extends ServiceProvider
{
    public function boot()
    {
		$router = app('router'); // Router Instance
        parent::boot();
		\Config::set('attachments.types.shift.entity', ShiftAttachment::class);
		
    }
        
    public function map(Router $router)
    {
        /**
         * @SWG\Get(
         *     tags={"Verifications"},
         *     path="/users/{userId}/signatures/{signatureId}",
         *     summary="Get a user signature by ID",
         *     description="Get a signature by ID",
         *     @SWG\Response(response="200", description="A user signature")
         * )
         */
        $router->get('/users/{userId}/signatures/{signatureId}', [
            'middleware' => [
                'instructorCanViewAllData',
            ],
            'as'         => 'shifts.signatures.show',
            'uses'       => SignaturesController::class . '@show'
        ]);


        /**
         * @SWG\Get(
         *     tags={"Shifts"},
         *     path="/users/{userId}/signatures",
         *     summary="Get a list of signatures for a shift",
         *     description="Get a list of signatures for a shift",
         *     @SWG\Parameter(name="shiftId", in="path", required=true, type="integer"),
         *     @SWG\Parameter(name="includes", in="query", type="array", items=@SWG\Items(type="string"),
         *     enum={"verifications"}, collectionFormat="csv"),
         *     @SWG\Parameter(name="includeIds", in="query", type="array", items=@SWG\Items(type="string"),
         *     enum={"verifications"}, collectionFormat="csv"),
         *     @SWG\Response(response="200", description="A list of shift signatures")
         * )
         */
        $router->get('/users/{userId}/signatures', [
            'middleware' => [
                'instructorCanViewAllData',
                'shiftStudentProgramMatchesUserContextProgram',
                'studentHasSkillsTrackerOrScheduler'
            ],
            'as'         => 'shifts.signatures.index',
            'uses'       => SignaturesController::class . '@store'
        ]);


        /**
         * 
         */
        $router->post('/users/{userId}/signatures', [
//            'middleware' => [
//                'shiftStudentProgramMatchesUserContextProgram',
//                'studentHasSkillsTrackerOrScheduler',
//                'instructorHasWritePermissionForShiftType',
//            ],
            'as'     => 'users.userId.signatures',
            'uses'   => SignaturesController::class . '@store'
        ]);


        /**
         * @SWG\Patch(
         *     tags={"Shifts"},
         *     path="/users/{userId}/signatures/{signatureId}",
         *     summary="Modify a shift signature",
         *     description="Modify a shift signature",
         *     @SWG\Parameter(name="shiftId", in="path", required=true, type="integer"),
         *     @SWG\Parameter(name="signatureId", in="path", required=true, type="integer"),
         *     @SWG\Parameter(name="signature", in="body", schema=@SWG\Schema(properties={
         *          @SWG\Property(property="nickname", type="string"),
         *          @SWG\Property(property="notes", type="string"),
         *          @SWG\Property(property="categories", type="array", items=@SWG\Items(type="string"))
         *     })),
         *     @SWG\Response(response="200", description="A modified shift signature")
         * )
         */
        $router->patch('/users/{userId}/signatures/{signatureId}', [
            'middleware' => [
                'instructorHasWritePermissionForShiftType'
            ],
            'as'     => 'shifts.signatures.update',
            'uses'   => SignaturesController::class . '@update'
        ]);


        /**
         * todo Adding a trailing slash on the path tricks Swagger into thinking the path is unique
         *
         * @SWG\Delete(
         *     tags={"Shifts"},
         *     path="/shifts/{shiftId}/signatures/{signatureIds}/",
         *     summary="Delete shift signatures",
         *     description="Delete shift signatures",
         *     @SWG\Parameter(name="shiftId", in="path", required=true, type="integer"),
         *     @SWG\Parameter(
         *      name="signatureIds", in="path", required=true, type="array",
         *      items=@SWG\Items(type="integer"), collectionFormat="csv"
         *     ),
         *     @SWG\Response(response="202", description="Accepted")
         * )
         */
        $router->delete('/shifts/{shiftId}/signatures/{signatureIds}', [
            'middleware' => [
                'instructorHasWritePermissionForShiftType'
            ],
            'as'     => 'shifts.signatures.destroy',
            'uses'   => SignaturesController::class . '@destroy'
        ]);
    }
}


