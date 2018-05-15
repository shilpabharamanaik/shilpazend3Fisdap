<?php namespace Fisdap\Api\Users\CurrentUser;

use Doctrine\ORM\EntityManager;
use Fisdap\Entity\User;

/**
 * Class ZendCurrentUser
 *
 * @package Fisdap\Api\Users\CurrentUser
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @todo move to Members codebase
 */
class ZendCurrentUser extends CommonCurrentUser
{
    /**
     * @var \Zend_Session_Namespace
     */
    private $session;


    /**
     * ZendCurrentUser constructor.
     *
     * @param EntityManager  $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        $this->session = new \Zend_Session_Namespace('currentUser', true);
    }


    /**
     * @return User
     */
    public function user()
    {
        return $this->session->user;
    }


    /**
     * @param User $user
     *
     * @return void
     */
    public function setUser(User $user)
    {
        $this->session->user = $user;
    }
}
