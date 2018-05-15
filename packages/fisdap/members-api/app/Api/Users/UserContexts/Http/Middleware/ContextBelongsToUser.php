<?php namespace Fisdap\Api\Users\Http\Middleware;

use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Closure;
use Fisdap\Api\Users\UserContexts\Http\Exceptions\ContextUserMismatch;
use Fisdap\Data\User\UserContext\UserContextRepository;
use Fisdap\Entity\UserContext;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;

/**
 * Ensures user context belongs to 'userId' route parameter
 *
 * @package Fisdap\Api\Users\Http\Middleware
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class ContextBelongsToUser
{

    /**
     * @var UserContextRepository
     */
    private $userContextRepository;

    /**
     * @param AuthManager $auth
     * @param UserContextRepository $userContextRepository
     */
    public function __construct(AuthManager $auth, UserContextRepository $userContextRepository)
    {
        $this->userContextRepository = $userContextRepository;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return
     */
    public function handle($request, Closure $next)
    {
        $userId = $request->route()->getParameter('userId');
        $userContextId = $request->route()->getParameter('userContextId');

        /** @var UserContext $context */
        $context = $this->userContextRepository->find($userContextId);

        if (empty($context)) {
            throw new ResourceNotFound("No User Context found with id '$userContextId'");
        }

        if ($context->getUser()->getId() != $userId) {
            throw new ContextUserMismatch('User Context ID does not belong to User');
        }

        return $next($request);
    }
}
