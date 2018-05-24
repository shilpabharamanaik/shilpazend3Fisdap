<?php namespace Fisdap\Attachments\Http;

use Doctrine\Common\Inflector\Inflector;
use Fisdap\Attachments\Commands\Creation\CreateAttachmentCommand;
use Fisdap\Attachments\Commands\Deletion\DeleteAttachmentsCommand;
use Fisdap\Attachments\Commands\Modification\ModifyAttachmentCommand;
use Fisdap\Attachments\Http\Requests\UpdateAttachmentRequest;
use Fisdap\Attachments\Http\Requests\StoreAttachmentRequest;
use Fisdap\Attachments\Queries\FindsAttachments;
use Fisdap\Attachments\Transformation\AttachmentTransformerFactory;
use Fisdap\ErrorHandling\Exceptions\UploadException;
use Fisdap\Fractal\CommonInputParameters;
use Fisdap\Fractal\ResponseHelpers;
use Illuminate\Bus\Dispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Routing\Controller;
use League\Fractal\Manager;

/**
 * Handles HTTP transport and data transformation for all attachment-specific routes
 *
 * @package Fisdap\Attachments\Http
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class AttachmentsController extends Controller
{
    use CommonInputParameters, ResponseHelpers;


    /**
     * @var FindsAttachments
     */
    private $finder;

    /**
     * @var AttachmentTransformerFactory
     */
    private $attachmentTransformerFactory;

    /**
     * @var string
     */
    private $attachmentType;


    /**
     * @param FindsAttachments             $finder
     * @param Manager                      $fractal
     * @param AttachmentTransformerFactory $attachmentTransformerFactory
     */
    public function __construct(
        FindsAttachments $finder,
        Manager $fractal,
        AttachmentTransformerFactory $attachmentTransformerFactory
    ) {
        $this->finder = $finder;
        $this->fractal = $fractal;
        $this->attachmentTransformerFactory = $attachmentTransformerFactory;
    }


    /**
     * @inheritdoc
     */
    public function callAction($method, $parameters)
    {
        // get attachment type from route name
        $routeNameParts = explode('.', static::$router->currentRouteName());
        $this->attachmentType = Inflector::singularize($routeNameParts[0]);

        // setup transformer
        $this->transformer = $this->attachmentTransformerFactory->create($this->attachmentType);
        $this->transformer->setAttachmentType($this->attachmentType);

        return parent::callAction($method, $parameters);
    }


    /**
     * @param int $associatedEntityId
     *
     * @return JsonResponse
     */
    public function index($associatedEntityId)
    {
        $attachments = $this->finder->findAllAttachments(
            $this->attachmentType,
            $associatedEntityId,
            $this->initAndGetIncludes(),
            $this->getIncludeIds()
        );

        return $this->respondWithCollection($attachments, $this->transformer);
    }


    /**
     * @param string $id
     *
     * @return JsonResponse
     */
    public function show($associatedEntityId, $id)
    {
        $attachment = $this->finder->findAttachment(
            $this->attachmentType,
            $id,
            $this->initAndGetIncludes(),
            $this->getIncludeIds(),
            true
        );

        return $this->respondWithItem($attachment, $this->transformer);
    }


    /**
     * @param int                    $associatedEntityId
     * @param Dispatcher             $dispatcher
     * @param StoreAttachmentRequest $request
     *
     * @return JsonResponse
     */
    public function store($associatedEntityId, Dispatcher $dispatcher, StoreAttachmentRequest $request)
    {
        $attachmentError = $request->file('attachment')->getError();

        if ($attachmentError > 0) {
            throw new UploadException($attachmentError);
        }

        $attachment = $dispatcher->dispatch(new CreateAttachmentCommand(
            $this->attachmentType,
            $associatedEntityId,
            $request->get('userContextId', $request->get('userRoleId')), //todo - remove support for userRoleId
            $request->file('attachment'),
            $request->get('id'),
            $request->get('nickname'),
            $request->get('notes'),
            $request->get('categories')
        ));

        $this->setStatusCode(HttpResponse::HTTP_CREATED);

        return $this->respondWithItem($attachment, $this->transformer);
    }


    /**
     * @param int                     $associatedEntityId
     * @param string                  $id
     * @param Dispatcher              $dispatcher
     * @param UpdateAttachmentRequest $request
     *
     * @return JsonResponse
     */
    public function update($associatedEntityId, $id, Dispatcher $dispatcher, UpdateAttachmentRequest $request)
    {
        $attachment = $dispatcher->dispatch(new ModifyAttachmentCommand(
            $this->attachmentType,
            $associatedEntityId,
            $id,
            $request->json('nickname', false),
            $request->json('notes', false),
            $request->json('categories', [])
        ));

        return $this->respondWithItem($attachment, $this->transformer);
    }


    /**
     * @param int        $associatedEntityId
     * @param string     $ids
     * @param Dispatcher $dispatcher
     *
     * @return JsonResponse
     */
    public function destroy($associatedEntityId, $ids, Dispatcher $dispatcher)
    {
        $dispatcher->dispatch(new DeleteAttachmentsCommand($this->attachmentType, explode(',', $ids)));

        $this->setStatusCode(HttpResponse::HTTP_ACCEPTED);

        return new HttpResponse('', $this->getStatusCode());
    }
}