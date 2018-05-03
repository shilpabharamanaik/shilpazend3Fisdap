<?php namespace Fisdap\Api\Users\CurrentUser;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Fisdap\Entity\User;
use Fisdap\Entity\UserContext;


/**
 * Class CommonCurrentUser
 *
 * @package Fisdap\Api\Users\CurrentUser
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
abstract class CommonCurrentUser implements CurrentUser
{
    /**
     * @var EntityManager
     */
    protected $entityManager;


    /**
     * @inheritdoc
     */
    public function getWritableUser()
    {
        if (is_null(($this->user()))) return null;

        $userId = $this->user()->getId();

        return $this->entityManager->find(User::class, $userId);
    }


    /**
     * @inheritdoc
     */
    public function reload()
    {
        if (is_null(($this->user()))) return;

        $userId = $this->user()->getId();

        /** @var User $user */
        $user = $this->entityManager->find(User::class, $userId);

        $this->entityManager->refresh($user);

        $this->setUser($user);
    }


    /**
     * @inheritdoc
     */
    public function context()
    {
        if (is_null($this->user())) return null;

        return $this->user()->getCurrentUserContext();
    }


    /**
     * @inheritdoc
     */
    public function setContext(UserContext $userContext)
    {
        /** @var User $user */
        $user = $this->entityManager->find(User::class, $this->user()->getId());

        $user->setCurrentUserContext($userContext);
        $this->entityManager->getRepository(User::class)->update($user);
        $this->setUser($user);
    }


    /**
     * @inheritdoc
     */
    public function setContextFromId($userContextId)
    {
        /** @var User $user */
        $user = $this->entityManager->find(User::class, $this->user()->getId());

        $userContextCriteria = Criteria::create();
        $userContextCriteria->where(Criteria::expr()->eq('id', $userContextId));
        $userContext = $user->getAllUserContexts()->matching($userContextCriteria)->first();

        if ( ! $userContext instanceof UserContext) {
            $userContext = $user->getAllUserContexts()->first();
        }

        $this->setContext($userContext);
    }
}