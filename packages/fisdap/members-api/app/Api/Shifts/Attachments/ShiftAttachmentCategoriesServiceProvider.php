<?php namespace Fisdap\Api\Shifts\Attachments;

use Fisdap\Attachments\Categories\AttachmentCategoriesController;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

/**
 * Enables shift attachment category-related routes, providing REST API endpoint documentation for each
 *
 * @package Fisdap\Api\Shifts\Attachments
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 *
 * @SWG\Definition(definition="ShiftAttachmentCategory", properties={
 *     @SWG\Property(property="id", type="integer"),
 *     @SWG\Property(property="name", type="string")
 * })
 */
final class ShiftAttachmentCategoriesServiceProvider extends ServiceProvider
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
     * @param Router $router
     */
    public function map(Router $router)
    {
        /**
         * @SWG\Get(
         *     tags={"Shifts"},
         *     path="/shifts/attachments/categories",
         *     summary="List all shift attachment categories",
         *     description="List all shift attachment categories",
         *     @SWG\Response(
         *      response="200",
         *      description="A list of shift attachment categories",
         *      schema=@SWG\Schema(
         *          properties={
         *              @SWG\Property(
         *                  property="data", type="array", items=@SWG\Items(
         *                      ref="#/definitions/ShiftAttachmentCategory"
         *                  )
         *              )
         *          }
         *      )
         *  )
         * )
         */
        $router->get('/shifts/attachments/categories', [
            'middleware' => [
                'instructorCanViewAllData',
                'studentHasSkillsTrackerOrScheduler',
            ],
            'as'         => 'shifts.attachments.categories.index',
            'uses'       => AttachmentCategoriesController::class . '@index'
        ]);


        /**
         * @SWG\Post(
         *     tags={"Shifts"},
         *     path="/shifts/attachments/categories",
         *     summary="Create shift attachment categories",
         *     description="Create shift attachment categories",
         *     @SWG\Parameter(name="AttachmentCategories", in="body", schema=@SWG\Schema(properties={
         *          @SWG\Property(property="names", type="array", items=@SWG\Items(type="string"))
         *     })),
         *     @SWG\Response(
         *      response="201",
         *      description="A list of created attachment categories",
         *      schema=@SWG\Schema(
         *          properties={
         *              @SWG\Property(
         *                  property="data", type="array", items=@SWG\Items(
         *                      ref="#/definitions/ShiftAttachmentCategory"
         *                  )
         *              )
         *          }
         *      )
         *  )
         * )
         */
        $router->post('/shifts/attachments/categories', [
            'middleware' => [
                'userMustBeStaff'
            ],
            'as'         => 'shifts.attachments.categories.store',
            'uses'       => AttachmentCategoriesController::class . '@store'
        ]);
        

        /**
         * @SWG\Delete(
         *     tags={"Shifts"},
         *     path="/shifts/attachments/categories/{ids}",
         *     summary="Delete shift attachment categories",
         *     description="Delete shift attachment categories",
         *     @SWG\Parameter(
         *      name="ids", in="path", required=true, type="array",
         *      items=@SWG\Items(type="integer"), collectionFormat="csv"
         *     ),
         *     @SWG\Response(
         *      response="200",
         *      description="Deletion count",
         *      schema=@SWG\Schema(properties={
         *          @SWG\Property(property="data", properties={
         *              @SWG\Property(property="deletionCount", type="integer")
         *          })
         *      }
         *    )
         *  )
         * )
         */
        $router->delete('/shifts/attachments/categories/{ids}', [
            'middleware' => [
                'userMustBeStaff'
            ],
            'as'         => 'shifts.attachments.categories.destroy',
            'uses'       => AttachmentCategoriesController::class . '@destroy'
        ]);
    }
}
