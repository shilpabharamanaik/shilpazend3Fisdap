<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for the legacy TestScores table.
 *
 * @Entity(repositoryClass="Fisdap\Data\TestScore\DoctrineTestScoreLegacyRepository")
 * @Table(name="TestScores")
 */
class TestScoreLegacy extends EntityBaseClass
{
    /**
     * @Id
     * @Column(name="TestScore_id", type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @ManyToOne(targetEntity="StudentLegacy")
     * @JoinColumn(name="Student_id", referencedColumnName="Student_id")
     */
    protected $student;
    
    /**
     * @ManyToOne(targetEntity="TestTypeLegacy")
     * @JoinColumn(name="TestType_id", referencedColumnName="TestType_id")
     */
    protected $test_type;

    /**
     * @Column(name="TestScore", type="integer")
     */
    protected $test_score;
    
    /**
     * @Column(name="PassOrFail", type="integer")
     */
    protected $pass_or_fail;
    
    /**
     * @Column(name="EntryTime", type="datetime")
     */
    protected $entry_time;
    
    
    /**
     * Setters
     */
    public function set_test_type($testType)
    {
        $this->test_type = self::id_or_entity_helper($testType, 'TestTypeLegacy');
    }
    
    public function set_student($student)
    {
        $this->student = self::id_or_entity_helper($student, 'StudentLegacy');
    }
}
