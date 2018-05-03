<?php namespace User\Entity;

use Doctrine\ORM\Proxy\Proxy;
use Doctrine\ORM\QueryBuilder;
//use Fisdap\EntityUtils;


/**
 * A base class for all Fisdap entities to extend from.
 * @deprecated
 * @author astevenson
 */
class EntityBaseClass
{
    /**
     * Tells if model uses DNAD (Deleted not actually deleted field)
     *
     * @var Boolean
     */
    protected $useDNADFlag = true;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $entityRepository;

    /**
     * @var string|null
     */
    private $uuid = null;

    public function setUUID($uuid = null)
    {
        $this->uuid = !is_numeric($uuid) ? $uuid : null;
    }

    public function getUUID()
    {
        return $this->uuid;
    }

    /**
     * Triggers the init method to be called upon creation of any NEW entity
     */
    public function __construct()
    {
        $this->init();

        // Cache the field mappings
    }


    /**
     * Empty stub to be overwritten/implemented in child classes
     * @codeCoverageIgnore
     * @deprecated
     * @todo find and remove all overrides
     * this method is totally redundant...all logic contained herein should be moved to the constructor ~bgetsug
     */
    protected function init()
    {
        //do nothing
    }


    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getEntityRepository()
    {
        return EntityUtils::getEntityManager()->getRepository(get_class($this));
    }


    /**
     * Saves all current entities back to the database.  Can be triggered
     * from any entity, so use caution.  If you do not want everything to be
     * committed yet, pass in false for the argument.  That will cause all
     * changes to be registered, but not actually pushed down to the database.
     *
     * @param Boolean $flush Determines whether or not to immediately push
     *                       the save out to the database.  If you pass in false, you will need to
     *                       remember to explicitly flush when you expect it to update the DB.
     */
    public function save($flush = true)
    {
        EntityUtils::getEntityManager()->persist($this);

        if ($flush) {
            $this->flush();
        }
    }


    /**
     * This method deletes an entity from the database.  Can be triggered from
     * an entity to effectively delete itself.
     *
     * @param Boolean $flush Determines whether or not to immediately push
     *                       the delete out to the database.  If you pass in false, you will need to
     *                       remember to explicitely flush when you expect it to update the DB.
     */
    public function delete($flush = true)
    {
        EntityUtils::getEntityManager()->remove($this);

        if ($flush) {
            $this->flush();
        }
    }


    /**
     * This is just a wrapper method around the EntityManager::flush() method.
     */
    public function flush()
    {
        EntityUtils::getEntityManager()->flush();
    }


    /**
     * Magic __get function override.  Basically, if a request comes into
     * this object for the given $property name, return that protected field.
     *
     * Throws a generic Exception if attempting to access a non-database field
     * through this method.
     *
     * @param String $property name of the property to fetch from the object
     *
     * @return Mixed Property of the object.
     */
    public function __get($property)
    {
			$getter = 'get_' . $property; //ucfirst($property);
			if (method_exists($this, $getter)) {
				return $this->$getter();
        } else {
            return $this->$property;
        }
    }


    /**
     * This magic function is invoked whenever a user tries to set a value
     * on an Entity.  Captures that call and saves the value onto the correct
     * property.
     *
     * Throws a generic Exception if attempting to access a non-database field
     * through this method.
     *
     * @param String $property Name of the property to set
     * @param Mixed  $value    Value to be saved into that property.
     *
     * @throws \Exception
     */
    public function __set($property, $value)
    {
        $setter = 'set_' . $property; //ucfirst($property);
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } else {
            /*
            // todo - determine if this is actually needed
            $metadata = \Fisdap\EntityUtils::getEntityManager()->getMetadataFactory()->getMetadataFor(get_class($this));
            $mappings = $metadata->fieldMappings;

            if ($mappings[$property]['type'] != 'string' && $mappings[$property]['type'] != 'text' && $value === "") {
                $value = null;
            }
            */

            $this->$property = $value;
        }
    }


    /**
     * This function checks to see if the given field is a database field
     * defined using doctrines metadata.
     *
     * @param string $field containing the name of the field to test
     *
     * @return bool
     */
    public function isDatabaseField($field)
    {
        return EntityUtils::isDatabaseField(get_class($this), $field);

        //$data = \Fisdap\EntityUtils::getEntityMetadata(get_class($this));
        //return (isset($data->reflFields[$field]));
        //$fields = array_keys($data->reflFields);		return in_array($field, $fields);
    }


    /**
     * Bringing this over for legacy code, with a changed name to fit new
     * standards.
     *
     * @return Array containing the names of all available fields on the entity.
     * Note that this returns Entity fields- not table fields.  There should be
     * a high correspondence between the two, but this might not always be the
     * case and should not be assumed that this is the case.
     *
     * The mapping is the same as legacy- DB field name as the key, object prop
     * name as the value at that index.
     */
    public function getFieldmap()
    {
        $data = EntityUtils::getEntityMetadata(get_class($this));

        return $data->fieldNames;
    }


    /**
     * Informs if current model uses DNAD (deleted not actually deleted) functionality
     *
     * @return boolean usesDNADFlag
     */
    public function isUsingDNAD()
    {
        return $this->useDNADFlag;
    }


    /**
     *    Runs all getters and returns array of values in form of
     *    field_name => field_value
     *
     * @return array values
     */
    public function toArray()
    {
        $ret = [];

        $fields = $this->getFieldmap();

        $ret['uuid'] = $this->getUUID();
        foreach ($fields as $field) {
            $ret[$field] = $this->$field;
        }

        return $ret;
    }


    /**
     * This function fetches and returns a new instance of the query builder.
     *
     * @return QueryBuilder instance.
     */
    public function getQueryBuilder()
    {
        return EntityUtils::getEntityManager()->createQueryBuilder();
    }


    /**
     * This is a shortcut method for executing a DQL query on an entity.
     *
     * If a QueryBuilder instance is provided, this function will determine if
     * the entity has DNAD enabled, and if it does, add on the flag to check
     * that.
     *
     * @param mixed $dql DQL to have doctrine run, or an instance of the
     *                   Doctrine2 QueryBuilder class.  See
     *                   http://www.doctrine-project.org/docs/orm/2.0/en/reference/dql-doctrine-query-language.html
     *                   for more information on DQL, and
     *                   http://www.doctrine-project.org/docs/orm/2.0/en/reference/query-builder.html
     *                   for more information on QueryBuilder.
     *
     * @return Array containing the requested data.
     */
    protected function runQuery($dql, $args = null)
    {
        // It needs to be an instance of a query builder, first of all...
        if ($dql instanceof QueryBuilder) {

            // If it's a select query, and this entity needs to be using DNAD
            // for this to take effect.
            if ($dql->getType() == QueryBuilder::SELECT &&
                $this->isUsingDNAD()
            ) {
                // Set the query to use the DNAD field...
                $alias = $dql->getRootAlias();
                $dql->where($alias . '.' . \Util_Db::getDNADFieldName() . ' = 1');
            }

            $toExecute = $dql->getDql();
        } else {
            $toExecute = $dql;
        }

        // Handy little guy to see just what the f it's actually running.
        // var_dump($toExecute);

        $query = EntityUtils::getEntityManager()->createQuery($toExecute);

        if (is_array($args)) {
            foreach ($args as $name => $val) {
                $query->setParameter($name, $val);
            }
        }

        return $query->getResult();
    }


    /**
     * Checks to see if the input is an ID, if so, it returns the associated
     * entity. If it's not an ID, check to make sure it's an entity.
     *
     * @param mixed  $id either an ID or an Entity
     * @param string $entityName
     *
     * @return EntityBaseClass
     * @throws \Exception
     * @codeCoverageIgnore
     * @deprecated 
     */
    public static function id_or_entity_helper($id, $entityName)
    {
        if (is_null($id)) {
            return null;
        }

        if (is_numeric($id)) {
            return EntityUtils::getEntity($entityName, $id);
        }

        if ($id instanceof EntityBaseClass) {
            return $id;
        }

        if ($id instanceof Proxy) {
            return $id;
        }

        throw new \Exception('Expected input of type int or \Fisdap\Entity\EntityBaseClass');
    }


    /**
     * Checks to see if the input is a string, if so, it returns the associated
     * DateTime object. If it's not a string, check to make sure it's a DateTime.
     *
     * @param mixed $value either a date string or a DateTime
     *
     * @return \DateTime
     * @throws \Exception
     */
    public static function string_or_datetime_helper($value)
    {
        if (empty($value)) {
            return null;
        }

        if (is_string($value)) {
            $datetime = new \DateTime($value);

            return $datetime;
        }

        if ($value instanceof \DateTime) {
            return $value;
        }

        throw new \Exception("Expected input of type string or DateTime");
    }


    /**
     * This function iterates over all of the elements in $group and deletes
     * (un-persists) them from the database.
     *
     * @param type $group
     */
    public function deleteGroup($group)
    {
        foreach ($group as $g) {
            $g->delete(false);
        }

        $this->flush();
    }


    /**
     * This function will return the name of the entity without the namespace.
     */
    public function getShortName()
    {
        $name = get_class($this);
        $pieces = explode("\\", $name);

        return end($pieces);
    }
}
