<?php namespace Fisdap\Api\Shifts\Attachments\Http;

use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Shifts\Attachments\Http\Middleware\ShiftAttachmentCreationUserContextLimit;
use Fisdap\Fractal\ResponseHelpers;


/**
 * Handles HTTP transport for additional attachment-related routes specific to shifts
 *
 * @package Fisdap\Api\Attachments
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class ShiftAttachmentsController extends Controller
{
    use ResponseHelpers;


    /**
     * @var ShiftAttachmentCreationUserContextLimit
     */
    private $userContextLimitPolicy;


    /**
     * @param ShiftAttachmentCreationUserContextLimit $userContextLimitPolicy
     */
    public function __construct(ShiftAttachmentCreationUserContextLimit $userContextLimitPolicy)
    {
        $this->userContextLimitPolicy = $userContextLimitPolicy;
    }


    /**
     * @param int $userContextId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function remainingAllottedCount($userContextId)
    {
        return $this->respondWithDataArray(
            ['remainingAllottedCount' => $this->userContextLimitPolicy->remainingAllottedCount($userContextId)]
        );
    }
}