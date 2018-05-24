<?php namespace Fisdap\Api\Auth;

use Doctrine\Common\Collections\Criteria;
use Fisdap\Data\User\UserRepository;
use Fisdap\Entity\User;
use Fisdap\Entity\UserContext;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;


/**
 * Class OAuth2Guard
 *
 * @package Fisdap\Api\Auth
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class OAuth2Guard implements Guard
{
    use GuardHelpers;


    /**
     * @var UserRepository
     */
    private $userRepository;


    /**
     * Create a new authentication guard.
     *
     * @param UserProvider   $provider
     * @param UserRepository $userRepository
     */
    public function __construct(UserProvider $provider, UserRepository $userRepository)
    {
        $this->provider = $provider;
        $this->userRepository = $userRepository;
    }


    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        $user = null;

        if ( ! is_null($this->user)) {
            $user = $this->user;
        }

        return $user;
    }


    /**
     * Validate a user's credentials.
     *
     * @param  array $credentials
     *
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        /** @var User $user */
        $user = $this->provider->retrieveByCredentials($credentials);

        if ($user === null) {
            return false;
        }

        $user->setAccessToken($credentials['access_token']);
		echo "HERE";exit;
        $this->setUser($user);

        if ($credentials['userContextId'] > 0 && $user->getCurrentUserContext()->getId() !== $credentials['userContextId']) {
            $this->setContext($credentials['userContextId']);
        }

        return true;
    }


    /**
     * @param int $userContextId
     */
    private function setContext($userContextId)
    {
        /** @var User $user */
        $user = $this->user;
        
        $userContextCriteria = Criteria::create();
        $userContextCriteria->where(Criteria::expr()->eq('id', $userContextId));
        $userContext = $user->getAllUserContexts()->matching($userContextCriteria)->first();

        if ( ! $userContext instanceof UserContext) {
            $userContext = $user->getAllUserContexts()->first();
        }

        $user->setCurrentUserContext($userContext);
        $this->userRepository->update($user);
    }
}