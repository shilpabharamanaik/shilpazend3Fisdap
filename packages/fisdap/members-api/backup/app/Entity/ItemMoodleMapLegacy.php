<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="ItemMoodleMap")
 */
class ItemMoodleMapLegacy extends EntityBaseClass
{
    /**
     * @Column(name="ItemMoodleMap_id", type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @Id
     * @Column(name="Item_id", type="integer")
     */
    protected $item_id;
    
    /**
     * @Column(name="Moodle_id", type="integer")
     */
    protected $moodle_id;
    
    /**
     * @Column(name="MoodleQuiz_id", type="integer")
     */
    protected $moodle_quiz_id;
    
    /**
     * @Column(name="MoodleDBName", type="string")
     */
    protected $moodle_db_name;
    
    /**
     * @Column(name="MoodleDBPrefix", type="string")
     */
    protected $moodle_db_prefix;
}
