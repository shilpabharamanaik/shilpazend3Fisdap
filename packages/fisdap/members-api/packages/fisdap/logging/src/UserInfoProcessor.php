<?php namespace Fisdap\Logging;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;

/**
 * Monolog Processor that adds extra user-identifying information to log messages
 *
 * @package Fisdap\Logging
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class UserInfoProcessor
{
    /**
     * @var Guard
     */
    private $auth;


    /**
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }


    /**
     * @param  array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        $user = $this->auth->user();

        if ($user instanceof Authenticatable) {
            $record['extra'] = array_merge(
                $record['extra'],
                [
                    'user_id' => $user->getAuthIdentifier(),
                ]
            );
        }

        return $record;
    }
}
