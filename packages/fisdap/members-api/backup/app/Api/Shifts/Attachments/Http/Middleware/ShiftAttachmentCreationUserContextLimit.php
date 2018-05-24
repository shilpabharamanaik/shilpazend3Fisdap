<?php namespace Fisdap\Api\Shifts\Attachments\Http\Middleware;

use Closure;
use Fisdap\Api\Products\Finder\FindsProducts;
use Fisdap\Api\Shifts\Attachments\Http\Exceptions\ShiftAttachmentLimitReached;
use Fisdap\Attachments\Core\TypeMapper\MapsAttachmentTypes;
use Fisdap\Attachments\Exceptions\MissingUserContextId;
use Fisdap\Attachments\Repository\AttachmentsRepository;
use Fisdap\Data\User\UserContext\UserContextRepository;
use Fisdap\Entity\UserContext;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * Middleware that limits the number of attachments allowed per user role (context)
 *
 * @todo    refactor for additional clarity of use with ShiftAttachmentsController
 *
 * @package Fisdap\Api\Shifts\Attachments
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ShiftAttachmentCreationUserContextLimit
{
    const SKILLS_TRACKER_UNLIMITED_PRODUCT_NAME = 'Skills Tracker (Unlimited)';
    const SKILLS_TRACKER_LIMITED_PRODUCT_NAME = 'Skills Tracker (Limited)';
    const LIMIT_SKILLS_TRACKER_UNLIMITED = 500;
    const LIMIT_SKILLS_TRACKER_LIMITED = 100;

    /**
     * @var UserContextRepository
     */
    private $userContextRepository;

    /**
     * @var MapsAttachmentTypes
     */
    private $attachmentTypeMapper;

    /**
     * @var AttachmentsRepository
     */
    private $attachmentsRepository;

    /**
     * @var FindsProducts
     */
    private $productsFinder;


    /**
     * @param UserContextRepository $userContextRepository
     * @param MapsAttachmentTypes  $attachmentTypeMapper
     * @param AttachmentsRepository $attachmentsRepository
     * @param FindsProducts         $productsFinder
     */
    public function __construct(
        UserContextRepository $userContextRepository,
        MapsAttachmentTypes $attachmentTypeMapper,
        AttachmentsRepository $attachmentsRepository,
        FindsProducts $productsFinder
    ) {
        $this->userContextRepository = $userContextRepository;
        $this->attachmentTypeMapper = $attachmentTypeMapper;
        $this->attachmentsRepository = $attachmentsRepository;
        $this->productsFinder = $productsFinder;
    }


    /**
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     * @throws MissingUserContextId
     * @throws NotFoundHttpException
     */
    public function handle($request, Closure $next)
    {
        // get user info
        $userContextId = ! is_null($request->get('userContextId')) ? $request->get('userContextId') : $request->get('userRoleId');

        if ($userContextId === null) {
            throw new MissingUserContextId('A user context ID must be specified when creating attachments.');
        }

        $userContext = $this->userContextRepository->getOneById($userContextId);

        if ( ! $userContext instanceof UserContext) {
            throw new NotFoundHttpException(
                'Unable to enforce ' . self::class . " due to missing UserContext with id $userContextId"
            );
        }

        $userContextProductNames = $this->productsFinder->getUserContextProductNames($userContextId);

        $attachmentsCount = $this->attachmentsCount($userContextId);

        if ($this->productsFinder->hasProduct(self::SKILLS_TRACKER_UNLIMITED_PRODUCT_NAME, $userContextProductNames)) {
            $this->hasReachedLimit(
                $userContext,
                $attachmentsCount,
                self::SKILLS_TRACKER_UNLIMITED_PRODUCT_NAME,
                self::LIMIT_SKILLS_TRACKER_UNLIMITED
            );
        } elseif ($this->productsFinder->hasProduct(self::SKILLS_TRACKER_LIMITED_PRODUCT_NAME, $userContextProductNames)) {
            $this->hasReachedLimit(
                $userContext,
                $attachmentsCount,
                self::SKILLS_TRACKER_LIMITED_PRODUCT_NAME,
                self::LIMIT_SKILLS_TRACKER_LIMITED
            );
        }

        return $next($request);
    }


    /**
     * @param int $userContextId
     *
     * @return int
     */
    public function remainingAllottedCount($userContextId)
    {
        $userContextProductNames = $this->productsFinder->getUserContextProductNames($userContextId);

        if ($this->productsFinder->hasProduct(self::SKILLS_TRACKER_UNLIMITED_PRODUCT_NAME, $userContextProductNames)) {
            return self::LIMIT_SKILLS_TRACKER_UNLIMITED - $this->attachmentsCount($userContextId);
        }

        if ($this->productsFinder->hasProduct(self::SKILLS_TRACKER_LIMITED_PRODUCT_NAME, $userContextProductNames)) {
            return self::LIMIT_SKILLS_TRACKER_LIMITED - $this->attachmentsCount($userContextId);
        }

        return 0;
    }


    /**
     * get count of shift attachments for user role
     * note that in Doctrine 2.5, using the Criteria API to count elements in
     * the attachments collection on the UserContext entity should be possible
     *
     * @param $userContextId
     *
     * @return int
     */
    public function attachmentsCount($userContextId)
    {
        $attachmentEntityName = $this->attachmentTypeMapper->getAttachmentEntityClassName('shift');

        return $this->attachmentsRepository->getCountByUserContextId($userContextId, [$attachmentEntityName]);
    }


    /**
     * @param UserContext $userContext
     * @param int         $attachmentsCount
     * @param string      $product
     * @param int         $limit
     *
     * @throws ShiftAttachmentLimitReached
     */
    private function hasReachedLimit($userContext, $attachmentsCount, $product, $limit)
    {
        if ($attachmentsCount === $limit) {
            $username = $userContext->getUser()->getUsername();

            throw new ShiftAttachmentLimitReached(
                "$username context {$userContext->getId()} has reached the shift attachment limit of $limit for '$product'"
            );
        }
    }
}