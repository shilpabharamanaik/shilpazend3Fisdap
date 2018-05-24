<?php namespace Fisdap\Api\Shifts\Attachments;

use Config;
use Fisdap\Api\Shifts\Attachments\Entities\ShiftAttachment;
use Fisdap\Api\Shifts\Attachments\Http\ShiftAttachmentsController;
use Fisdap\Attachments\Http\AttachmentsController;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Swagger\Annotations as SWG;

/**
 * Enables shift attachment-related routes, providing REST API endpoint documentation for each, and provides mapping
 * configuration for 'shift' attachment type to ShiftAttachment Entity
 *
 * @package Fisdap\Api\Shifts\Attachments
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
final class ShiftAttachmentsServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    
	public function boot()
    {
		$router = app('router'); // Router Instance
        parent::boot();
		Config::set('attachments.types.shift.entity', ShiftAttachment::class);
    }

    /**
     * @param Router $router
     */
    public function map(Router $router)
    {
        /**
         * @SWG\Get(
         *     tags={"Shifts"},
         *     path="/shifts/{shiftId}/attachments/{attachmentId}",
         *     summary="Get a shift attachment by ID",
         *     description="Get a shift attachment by ID",
         *     @SWG\Parameter(name="shiftId", in="path", required=true, type="integer"),
         *     @SWG\Parameter(name="attachmentId", in="path", required=true, type="integer"),
         *     @SWG\Parameter(name="includes", in="query", type="array", items=@SWG\Items(type="string"),
         *     enum={"verifications"}, collectionFormat="csv"),
         *     @SWG\Parameter(name="includeIds", in="query", type="array", items=@SWG\Items(type="string"),
         *     enum={"verifications"}, collectionFormat="csv"),
         *     @SWG\Response(response="200", description="A shift attachment")
         * )
         */
        $router->get('/shifts/{shiftId}/attachments/{attachmentId}', [
            'middleware' => [
                'instructorCanViewAllData',
            ],
            'as'         => 'shifts.attachments.show',
            'uses'       => AttachmentsController::class . '@show'
        ]);


        /**
         * @SWG\Get(
         *     tags={"Shifts"},
         *     path="/shifts/{shiftId}/attachments",
         *     summary="Get a list of attachments for a shift",
         *     description="Get a list of attachments for a shift",
         *     @SWG\Parameter(name="shiftId", in="path", required=true, type="integer"),
         *     @SWG\Parameter(name="includes", in="query", type="array", items=@SWG\Items(type="string"),
         *     enum={"verifications"}, collectionFormat="csv"),
         *     @SWG\Parameter(name="includeIds", in="query", type="array", items=@SWG\Items(type="string"),
         *     enum={"verifications"}, collectionFormat="csv"),
         *     @SWG\Response(response="200", description="A list of shift attachments")
         * )
         */
        $router->get('/shifts/{shiftId}/attachments', [
            'middleware' => [
                'instructorCanViewAllData',
                'shiftStudentProgramMatchesUserContextProgram',
                'studentHasSkillsTrackerOrScheduler'
            ],
            'as'         => 'shifts.attachments.index',
            'uses'       => AttachmentsController::class . '@index'
        ]);


        /**
         * @SWG\Post(
         *     tags={"Shifts"},
         *     path="/shifts/{shiftId}/attachments",
         *     summary="Create a shift attachment",
         *     description="Create a shift attachment",
         *     @SWG\Parameter(name="shiftId", in="path", required=true, type="integer"),
         *     @SWG\Parameter(name="attachment", in="formData", required=true, type="file"),
         *     @SWG\Parameter(name="nickname", in="formData", required=true, type="string"),
         *     @SWG\Parameter(name="userContextId", in="formData", required=true, type="integer"),
         *     @SWG\Parameter(name="notes", in="formData", required=true, type="string"),
         *     @SWG\Parameter(
         *      name="categories", in="formData", required=true, type="array", collectionFormat="multi",
         *      items=@SWG\Items(type="string")
         *     ),
         *     @SWG\Response(response="201", description="A created shift attachment")
         * )
         */
        $router->post('/shifts/{shiftId}/attachments', [
            'middleware' => [
                'shiftAttachmentCreationUserContextLimit',
                'shiftStudentProgramMatchesUserContextProgram',
                'studentHasSkillsTrackerOrScheduler',
                'instructorHasWritePermissionForShiftType',
            ],
            'as'     => 'shifts.attachments.store',
            'uses'   => AttachmentsController::class . '@store'
        ]);


        /**
         * @SWG\Patch(
         *     tags={"Shifts"},
         *     path="/shifts/{shiftId}/attachments/{attachmentId}",
         *     summary="Modify a shift attachment",
         *     description="Modify a shift attachment",
         *     @SWG\Parameter(name="shiftId", in="path", required=true, type="integer"),
         *     @SWG\Parameter(name="attachmentId", in="path", required=true, type="integer"),
         *     @SWG\Parameter(name="Attachment", in="body", schema=@SWG\Schema(properties={
         *          @SWG\Property(property="nickname", type="string"),
         *          @SWG\Property(property="notes", type="string"),
         *          @SWG\Property(property="categories", type="array", items=@SWG\Items(type="string"))
         *     })),
         *     @SWG\Response(response="200", description="A modified shift attachment")
         * )
         */
        $router->patch('/shifts/{shiftId}/attachments/{attachmentId}', [
            'middleware' => [
                'instructorHasWritePermissionForShiftType'
            ],
            'as'     => 'shifts.attachments.update',
            'uses'   => AttachmentsController::class . '@update'
        ]);


        /**
         * @todo Adding a trailing slash on the path tricks Swagger into thinking the path is unique
         *
         * @SWG\Delete(
         *     tags={"Shifts"},
         *     path="/shifts/{shiftId}/attachments/{attachmentIds}/",
         *     summary="Delete shift attachments",
         *     description="Delete shift attachments",
         *     @SWG\Parameter(name="shiftId", in="path", required=true, type="integer"),
         *     @SWG\Parameter(
         *      name="attachmentIds", in="path", required=true, type="array",
         *      items=@SWG\Items(type="integer"), collectionFormat="csv"
         *     ),
         *     @SWG\Response(response="202", description="Accepted")
         * )
         */
        $router->delete('/shifts/{shiftId}/attachments/{attachmentIds}', [
            'middleware' => [
                'instructorHasWritePermissionForShiftType'
            ],
            'as'     => 'shifts.attachments.destroy',
            'uses'   => AttachmentsController::class . '@destroy'
        ]);


        /**
         * @SWG\Get(
         *     tags={"Users"},
         *     path="/users/contexts/{userContextId}/shifts/attachments/remaining",
         *     summary="Get a count of the remaining allotted shift attachments for a user context",
         *     description="Get a count of the remaining allotted shift attachments for a user context",
         *     @SWG\Parameter(name="userContextId", in="path", required=true, type="integer"),
         *     @SWG\Response(
         *      response="200",
         *      description="Remaining allotted count",
         *      schema=@SWG\Schema(properties={
         *          @SWG\Property(property="data", properties={
         *              @SWG\Property(property="remainingAllottedCount", type="integer")
         *          })
         *      }
         *    )
         *  )
         * )
         */
        $router->get('/users/contexts/{userContextId}/shifts/attachments/remaining', [
            'middleware' => [
                // let's not restrict this endpoint, so it returns the count as quickly as possible
            ],
            'as'         => 'users.contexts.shifts.attachments.remaining',
            'uses'       => ShiftAttachmentsController::class . '@remainingAllottedCount'
        ]);
    }
}
