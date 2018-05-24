<?php namespace Fisdap\Api\Attachments;

use Fisdap\Attachments\Categories\AttachmentCategoriesController;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Swagger\Annotations as SWG;


/**
 * Class ApiAttachmentsServiceProvider
 *
 * @package Fisdap\Api\Attachments
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
final class ApiAttachmentsServiceProvider extends ServiceProvider
{
    /**
     * @param Router $router
     */
    public function map(Router $router)
    {
        /**
         * @SWG\Get(
         *     tags={"Attachments"},
         *     path="/attachments/categories/{id}",
         *     summary="Get an attachment category by ID",
         *     description="Get an attachment category by ID",
         *     @SWG\Parameter(name="id", in="path", required=true, type="integer"),
         *     @SWG\Response(response="200", description="An attachment category")
         * )
         */
        $router->get('/attachments/categories/{id}', [
            'middleware' => [
                'userMustBeStaff'
            ],
            'as' => 'attachments.categories.show', 'uses' => AttachmentCategoriesController::class . '@show'
        ]);


        /**
         * @SWG\Patch(
         *     tags={"Attachments"},
         *     path="/attachments/categories/{id}",
         *     summary="Modify an attachment category",
         *     description="Modify an attachment category",
         *     @SWG\Parameter(name="id", in="path", required=true, type="integer"),
         *     @SWG\Parameter(name="AttachmentCategory", in="body", schema=@SWG\Schema(properties={
         *          @SWG\Property(property="newName", type="string")
         *     })),
         *     @SWG\Response(response="200", description="A modified attachment category")
         * )
         */
        $router->patch('/attachments/categories/{id}', [
            'middleware' => [
                'userMustBeStaff'
            ],
            'as' => 'attachments.categories.rename', 'uses' => AttachmentCategoriesController::class . '@rename'
        ]);
    }
}