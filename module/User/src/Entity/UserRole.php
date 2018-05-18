<?php

namespace User\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * This class represents a registered user role.
 * @ORM\Entity()
 * @ORM\Table(name="fisdap2_user_roles")
 */
class UserRole
{

    /**
     * @ORM\Id
     * @ORM\Column(name="id")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(name="user_id")
     */
    protected $userId;

    /**
     * @ORM\Column(name="role_id")
     */
    protected $roleId;

    /**
     * @ORM\Column(name="program_id")
     */
    protected $programId;

    /**
     * @ORM\Column(name="certification_level_id")
     */
    protected $certificationLevelId;

    /**
     * @ORM\Column(name="start_date")
     */
    protected $startDate;

    /**
     * @ORM\Column(name="end_date")
     */
    protected $endDate;

    /**
     * @ORM\Column(name="active")
     */
    protected $active;

    /**
     * @ORM\Column(name="email")
     */
    protected $email;

    /**
     * @ORM\Column(name="courseId")
     */
    protected $courseId;

    /**
     * Is this user role an instructor
     *
     * @return bool
     */
    public function isInstructor()
    {
        return $this->roleId == 2;
    }
}
