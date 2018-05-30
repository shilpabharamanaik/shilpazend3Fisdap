<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for Moodle Test informational documents
 *
 * @Entity
 * @Table(name="fisdap2_moodle_test_documents")
 */
class MoodleTestDocument extends File
{
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @ManyToOne(targetEntity="MoodleTestDataLegacy")
     * @JoinColumn(name="moodle_quiz_id", referencedColumnName="MoodleQuiz_id")
     */
    protected $test;

    /**
     * @Column(type="string")
     */
    protected $label;
    
    /*
     * SEtters and maybe Getters
     */
    public function set_test($value)
    {
        $this->test = self::id_or_entity_helper($value, "MoodleTestDataLegacy");
        return $this;
    }
}
