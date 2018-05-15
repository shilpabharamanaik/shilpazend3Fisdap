<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for the legacy TestPasswordData table.
 *
 * @Entity
 * @Table(name="TestPasswordData")
 */
class TestPasswordDataLegacy extends EntityBaseClass
{
    /**
     * @Id
     * @Column(name="password_entry_id", type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @ManyToOne(targetEntity="MoodleTestDataLegacy")
     * @JoinColumn(name="test_id", referencedColumnName="MoodleQuiz_id")
     */
    protected $test;
    
    /**
     * @Column(name="testdate", type="date")
     */
    protected $date;
    
    /**
     * @Column(name="password", type="string")
     */
    protected $password;
}
