<?php namespace Fisdap\Api\Programs\Events;

use Fisdap\Api\Events\Event;

/**
 * Class DemoStudentWasCreated
 *
 * @package Fisdap\Api\Programs\Events
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
final class DemoStudentWasCreated extends Event
{
    /**
     * @var int
     */
    private $programId;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;


    /**
     * DemoStudentWasCreated constructor.
     *
     * @param int    $programId
     * @param string $username
     * @param string $password
     */
    public function __construct($programId, $username, $password)
    {
        $this->programId = $programId;
        $this->username = $username;
        $this->password = $password;
    }


    /**
     * @return mixed
     */
    public function getProgramId()
    {
        return $this->programId;
    }


    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }


    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }
}
