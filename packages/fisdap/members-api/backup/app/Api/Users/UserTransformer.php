<?php namespace Fisdap\Api\Users;

use Fisdap\Entity\User;
use Fisdap\Fractal\Transformer;
use Fisdap\Api\Users\UserContexts\UserContextTransformer;


/**
 * Prepares user data for JSON output
 *
 * @package Fisdap\Api\Users
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class UserTransformer extends Transformer
{
    /**
     * @var string[]
     */
    protected $defaultIncludes = [
        'userRoles',
        'userContexts'
    ];


    /**
     * @param array|User $user
     *
     * @return array
     */
    public function transform($user)
    {
        if ($user instanceof User) {
            $user = $user->toArray();
        }

        $this->removeFields(['password', 'password_salt', 'legacy_password'], $user);

        if (!isset($user['uuid'])) {
            $this->removeFields(["uuid"], $user);
        }

        if (isset($user['birth_date'])) {
            $user['birth_date'] = $this->formatDateTimeAsString($user['birth_date']);
        }

        if (isset($user['updated_on'])) {
            $user['updated_on'] = $this->formatDateTimeAsString($user['updated_on']);
        }

        if (isset($user['staff'])) {
            $user['staff'] = true;
        }

        if (isset($user['emt_grad_date'])) {
            $user['emt_grad_date'] = $this->formatDateTimeAsString($user['emt_grad_date']);
        }

        if (isset($user['emt_cert_date'])) {
            $user['emt_cert_date'] = $this->formatDateTimeAsString($user['emt_cert_date']);
        }

        if (isset($user['license_expiration_date'])) {
            $user['license_expiration_date'] = $this->formatDateTimeAsString($user['license_expiration_date']);
        }

        if (isset($user['state_license_expiration_date'])) {
            $user['state_license_expiration_date'] = $this->formatDateTimeAsString($user['state_license_expiration_date']);
        }

        return $user;
    }


    /**
     * @param array|User $user
     *
     * @return \League\Fractal\Resource\Collection
     * @codeCoverageIgnore
     * @deprecated
     */
    public function includeUserRoles($user)
    {
        return $this->includeUserContexts($user);
    }


    /**
     * @param array|User $user
     *
     * @return \League\Fractal\Resource\Collection
     * @codeCoverageIgnore
     */
    public function includeUserContexts($user)
    {
        $userContexts = $user instanceof User ? $user->getUserContexts() : $user['userContexts'];

        return $this->collection($userContexts, new UserContextTransformer);
    }
} 