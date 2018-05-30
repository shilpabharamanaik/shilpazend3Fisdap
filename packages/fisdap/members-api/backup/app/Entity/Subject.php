<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Fisdap\EntityUtils;

/**
 * Entity class for Subject.
 *
 * @Entity
 * @Table(name="fisdap2_subject")
 */
class Subject extends Enumerated
{
    /**
     * @var string
     * @Column(type="string")
     */
    protected $type;
    
    public static function getFormOptions($na = false, $sort=true, $displayName = "name")
    {
        $options = array();
        
        $query = "SELECT DISTINCT s.name FROM \Fisdap\Entity\Subject s";
        $results = EntityUtils::getEntityManager()->createQuery($query)->getResult();
        
        if ($na) {
            $options[0] = "Unset";
        }
        
        foreach ($results as $result) {
            $options[$result['name']] = $result['name'];
        }
        
        return $options;
    }
    
    public static function getSelectOptions($unset = false)
    {
        $options = array();
        
        if ($unset) {
            $options[0] = "Unset";
        }
        
        foreach (self::getAllSubjectTypes() as $subject) {
            $options[$subject->id] = $subject->name . " (" . $subject->type. ")";
        }
        
        return $options;
    }
    
    protected $caption;
    
    public function get_caption()
    {
        return ucfirst($this->type) . ' ' . $this->name . 's';
    }
    
    public static function getAllSubjectTypes()
    {
        return EntityUtils::getRepository('Subject')->findAll();
    }
    
    public static function getAllSubjectTypeCaptions()
    {
        $types = self::getAllSubjectTypes();
        foreach ($types as $type) {
            echo $ret[$type->id] = $type->get_caption();
        }
        return $ret;
    }

    /**
     * Given a subject type name, return the number of types with that name
     */
    public static function getTypeCountByName($name)
    {
        return count(EntityUtils::getRepository('Subject')->findByName($name));
    }

    /**
     * Takes an array of subject type ids and spits out a nice verbal description of those types
     * Examples:
     *      array(1) => "Human (live)"
     *      array(1, 2) => "Human"
     *      array(2, 5, 6) => "Human (dead) and Manikin"
     *      array(1, 3, 5, 6) => "Human (live), Animal (live) and Manikin"
     *      array(1, 2, 3, 4, 5, 6) => "all"
     *
     * @param $options array of the ids of the subject type options we want described
     * @return string a legible description of the given subject types
     */
    public static function getSubjectTypeDescription($options)
    {
        $selectedTypes = array();
        $sortedTypes = array();
        $allTypes = self::getAllSubjectTypes();

        // if all possible subject types are chosen, return "all"
        if (count($options) >= count($allTypes)) {
            return "all";
        }

        // otherwise, get the descriptions of the options we're looking at and group them by name
        foreach ($allTypes as $type) {
            if (is_array($options) && in_array($type->id, $options)) {
                $selectedTypes[$type->name][] = $type->type;
            }
        }

        // now go through and see if we have all of any of the name groups
        foreach ($selectedTypes as $name => $types) {
            // if we have all types of this name group, just add the name
            if (count($types) == self::getTypeCountByName($name)) {
                $sortedTypes[] = $name;
            } else {
                // otherwise, add each type individually
                foreach ($types as $type) {
                    $sortedTypes[] = $name . " (" . $type . ")";
                }
            }
        }

        // return a comma separated list of the values, with an "and" between the last two, if applicable
        return \Util_String::addAndToList(implode(", ", $sortedTypes));
    }


    /**
     * @return array
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_merge(parent::toArray(), ['type' => $this->getType()]);
    }
}
