<?php namespace Fisdap\Members\Lti\Session;

/**
 * DTO for storing LTI user information in the Session
 *
 * @package Fisdap\Members\Lti\Session
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class LtiSessionUser
{
    const INSTRUCTOR_ROLE = 'instructor';

    const STUDENT_ROLE = 'student';

    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * @var string
     */
    public $email;

    /**
     * @var int|null
     */
    public $courseId = null;

    /**
     * @var int
     */
    public $programId;

    /**
     * @var array
     */
    public $isbns = [];

    /**
     * @var string
     */
    public $role;
}
