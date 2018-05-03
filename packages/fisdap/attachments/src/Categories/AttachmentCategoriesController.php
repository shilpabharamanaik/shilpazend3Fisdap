<?php namespace Fisdap\Attachments\Categories;

use Doctrine\Common\Inflector\Inflector;
use Fisdap\Attachments\Categories\Commands\Creation\CreateAttachmentCategoriesCommand;
use Fisdap\Attachments\Categories\Commands\Deletion\DeleteAttachmentCategoriesCommand;
use Fisdap\Attachments\Categories\Commands\Modification\RenameAttachmentCategoryCommand;
use Fisdap\Attachments\Categories\Entity\AttachmentCategoryNotFound;
use Fisdap\Attachments\Categories\Queries\FindsAttachmentCategories;
use Fisdap\Fractal\ResponseHelpers;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Router;
use League\Fractal\Manager;

/**
 * Handles HTTP transport and data transformation for all attachment category-specific routes
 *
 * @package Fisdap\Attachments\Categories
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class AttachmentCategoriesController extends Controller
{
    use ResponseHelpers;


    /**
     * @var Request
     */
    private $request;

    /**
     * @var FindsAttachmentCategories
     */
    private $finder;

    /**
     * @var string
     */
    private $attachmentType;


    /**
     * @param Router                        $router
     * @param Request                       $request
     * @param FindsAttachmentCategories     $finder
     * @param Manager                       $fractal
     * @param AttachmentCategoryTransformer $transformer
     */
    public function __construct(
        Router $router,
        Request $request,
        FindsAttachmentCategories $finder,
        Manager $fractal,
        AttachmentCategoryTransformer $transformer
    ) {
        self::setRouter($router);
        $this->request = $request;

        $this->finder = $finder;
        $this->fractal = $fractal;
        $this->transformer = $transformer;

        $this->attachmentType = $this->getAttachmentTypeFromRouteName();
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $attachments = $this->finder->findAll($this->attachmentType);

        return $this->respondWithCollection($attachments, $this->transformer);
    }


    /**
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws AttachmentCategoryNotFound
     */
    public function show($id)
    {
        $attachmentCategory = $this->finder->findById($id);

        if ($attachmentCategory === null) {
            throw new AttachmentCategoryNotFound("No attachment category with ID $id was found");
        }

        return $this->respondWithItem($attachmentCategory, $this->transformer);
    }


    /**
     * @param Dispatcher $dispatcher
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Dispatcher $dispatcher)
    {
        $attachmentCategories = $dispatcher->dispatch(
            new CreateAttachmentCategoriesCommand($this->attachmentType, $this->request->json('names'))
        );

        $this->setStatusCode(HttpResponse::HTTP_CREATED);

        return $this->respondWithCollection($attachmentCategories, $this->transformer);
    }


    /**
     * @param int        $id
     * @param Dispatcher $dispatcher
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function rename($id, Dispatcher $dispatcher)
    {
        $attachmentCategory = $dispatcher->dispatch(
            new RenameAttachmentCategoryCommand($this->request->json('newName'), null, $id)
        );

        return $this->respondWithItem($attachmentCategory, $this->transformer);
    }


    /**
     * @param int        $ids
     * @param Dispatcher $dispatcher
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($ids, Dispatcher $dispatcher)
    {
        $deletionCount = $dispatcher->dispatch(
            new DeleteAttachmentCategoriesCommand(
                $this->attachmentType,
                isset($ids) ? explode(',', $ids) : null
            )
        );

        return $this->respondWithDataArray(['deletionCount' => $deletionCount]);
    }


    /**
     * @return string
     */
    private function getAttachmentTypeFromRouteName()
    {
        $routeNameParts = explode('.', static::$router->currentRouteName());
        $attachmentType = Inflector::singularize($routeNameParts[0]);

        return $attachmentType;
    }
}
