<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Fisdap\EntityUtils;

/**
 * Base class for enumerated tables
 * @MappedSuperclass
 */
class Enumerated extends EntityBaseClass
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @Column(type="string")
     */
    protected $name;

    /**
     * This function provides an easy way to get an array to use in dropdown
     * select boxes.
     *
     * @param Boolean $na Determines whether or not to include an "N/A" option
     * in the list. Defaults to false.
     * @param Boolean $sort Determines whether or not to sort the returning list/
     * Defaults to true.
     * @param string $displayName The field name that we should output, defaults to "name"
     *
     * @return array containing the requested list of entities, with the index
     * being the ID of the entity, and the value at that index the name field of
     * the entity.
     */
    public static function getFormOptions($na = false, $sort=true, $displayName = "name")
    {
        $options = array();
        $repo = EntityUtils::getEntityManager()->getRepository(get_called_class());
        $results = $repo->findAll();

        foreach ($results as $result) {
            if ($result->id != -1) {
                $tempOptions[$result->id] = $result->$displayName;
            }
        }

        if ($sort) {
            asort($tempOptions);
        }

        if ($na) {
            $options[0] = "N/A";
            foreach ($tempOptions as $id => $name) {
                $options[$id] = $name;
            }
        } else {
            $options = $tempOptions;
        }

        return $options;
    }

    /**
     *	Methods below get other columns, cache results
     */
    protected static $allData=array();

    public static function loadAll()
    {
        $class = get_called_class();
        if (!isset(self::$allData[$class])) {
            $repo = EntityUtils::getEntityManager()->getRepository($class);

            //Pull out values of -1
            $options = array();
            $tempOptions = $repo->findAll();
            foreach ($tempOptions as $option) {
                if ($option->id != -1) {
                    $options[] = $option;
                }
            }
            self::$allData[$class] = $options;
        }
        return $class;
    }

    /**
     *	Returns all values:
     *	@param boolean $asArray = false
     *		if true returns array of Entities
     *	@param string $which column, if specified will return only this column's values
     *	@todo move to EntityUtils would make sense
     */
    public static function getAll($asArray = false, $whichColumn='All')
    {
        $class = self::loadAll();

        if ($asArray) {
            if ($whichColumn=='All') {
                $fields = EntityUtils::getEntityFields($class, true);
                foreach (self::$allData[$class] as $row => $vals) {
                    $id = $vals->id;
                    foreach ($fields as $field) {
                        $ret[$id][$field] = $vals->$field;
                    }
                }
            } else {
                foreach (self::$allData[$class] as $row => $vals) {
                    $id = $vals->id;
                    $ret[$id] = $vals->$whichColumn;
                }
            }
            return $ret;
        } else {
            return self::$allData[$class];
        }
    }

    public static function getAllGroupedBy($column)
    {
        $class = self::loadAll();

        $ret=array();

        // columns exists?
        $firstRow = current(self::$allData[$class]);
        if (!isset($firstRow->$column)) {
            return null;
        }

        foreach (self::$allData[$class] as $vals) {
            //echo $vals->$column . '<br/>';
            if (!isset($ret[$vals->$column])) {
                $ret[$vals->$column] = array();
            }
            $ret[$vals->$column][] = $vals->id;
        }

        return $ret;
    }

    /**
     *	Allow to get any db field not defined here
     */
    public function getColumn($column)
    {
        return $this->$column;
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }


    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName()
        ];
    }
}
