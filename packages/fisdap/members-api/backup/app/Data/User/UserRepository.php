<?php namespace Fisdap\Data\User;

use Fisdap\Data\Repository\Repository;
use Fisdap\Entity\User;


/**
 * Interface UserRepository
 *
 * @package Fisdap\Data\User
 */
interface UserRepository extends Repository
{

    /**
     * @param string $username
     *
     * @return User
     */
    public function getOneByUsername($username);

    /**
     * @param array $usernames
     *
     * @return array
     */
    public function getByUsername(array $usernames);
}