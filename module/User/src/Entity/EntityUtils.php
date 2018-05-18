<?php namespace User\Entity;


/**
 * Collection of static methods handy for Entities.
 *
 * This class is deprecated because its use of static methods will make testing difficult, and it does not promote use
 * of dependency injection.  Going forward, reusable QueryBuilder logic should be encapsulated using Query or Specification
 * classes.  Additionally, any conversion of Entities to arrays should be handled with QueryBuilder or with a Transformer
 * or other View Presenter-like pattern.
 *
 * @author astevenson
 * @codeCoverageIgnore
 * @deprecated
 */
class EntityUtils
{
    /**
     * This function fetches and returns all of the collected metadata for the
     * entity.
     *
     * @param $className
     *
     * @return Mixed Doctrine\ORM\Mapping\ClassMetadata on success, Boolean
     * false on failure
     * @deprecated This method is a redundant wrapper for functionality available through EntityManager directly
     */
    public static function getEntityMetadata($className)
    {
        if(!class_exists($className)){
            // Try to add on the namespace, see if that helps...
            $className = self::getFullEntityName($className);
        }

        $entityManager = self::getEntityManager();
        $metadata = $entityManager->getMetadataFactory()->getMetadataFor($className);

        return $metadata;
    }

    /**
     * This function loads up the specified entity and returns it for use.
     * If an ID is passed in, that specific instance of the entity is returned.
     *
     * @param String $entityName Name of the entity type to return (i.e. 'User',
     *							 'ShiftLegacy', etc.).
     * @param mixed $id (Integer or array of integers) ID(s) of the record of the Entity to fetch.
     *
     * @return \Fisdap\Entity\EntityBaseClass instance with the requested data, if an ID
     *		   was provided and found, or an empty instance if no ID given, or
     *		   return null if no record with that ID was found.
     * @deprecated Entities should only be retrieved through Repositories or other Aggregate Roots.  Other services
     *             would then depend on the appropriate Repository.
     */
    public static function getEntity($entityName, $id=false)
    {
        // id can be array of ids too
        if(is_array($id)) {
            $entities = array();
            foreach ($id as $i => $oneId) {
                $entities[$i] = self::getEntity($entityName, $oneId);
            }

            return $entities;
        }

        $entityManager = self::getEntityManager();

        if(strpos($entityName, "\\") === false){
            $fullEntityName = self::getFullEntityName($entityName);
        }else{
            $fullEntityName = $entityName;
        }

        $ent = null;


        //If an ID wasn't passed in, just return an empty entity
        if ($id === false) {
            $ent = new $fullEntityName();

            return $ent;
        }

        // Try to set ent to the result of the find...
        try {
            $ent = $entityManager->find($fullEntityName, $id);
        } catch (\Exception $e) {
            return null;
        }

        // If we can't find one, just return null
        if ($ent == null) {
            return null;
        }

        return $ent;
    }


    /**
     *    Purpose is to get entities for manually run query results. Ex: If in our manual
     *    query we are searching for users and are getting user ids, use this method
     *    to get user entities.
     *
     * @param array  $results    database results
     * @param string $entityName
     * @param string $idField    entity id field
     * @param string $indexField (optional = do we care about link between row and entity)
     *                           if given results will be returned in array(indexFieldValue => $entity)
     *                           otherwise this method will return array of unique de-duplicated entities
     *                           de-duplicated means that if results contain duplicate entity ids
     *                           each entity will be returned only once
     *
     * @return array
     * @deprecated
     */
    public static function getEntitiesForQueryResults($results, $entityName, $idField=null, $indexField=null)
    {
        // default field name
        if (is_null($idField)) {
            $idField = strtolower($entityName) . '_id';
        }

        $entities = array();

        foreach ($results as $row) {
            if (!empty($row[$idField])) {
                $curEntityId = $row[$idField];
                if (is_null($indexField)) {		// no linking between rows and entities
                    if (!isset($entities[$curEntityId])) {
                        $entities[$curEntityId] = self::getEntity($entityName, $curEntityId);
                    }
                } else {						// linking between rows and entities
                    if (!isset($entities[$row[$indexField]])) {
                        $entities[$row[$indexField]] = self::getEntity($entityName, $curEntityId);
                    }
                }
            }
        }

        return $entities;
    }


    /**
     *	Variable to cache isDatabaseField check and allow adding other 'fields'
     *	  as database fields
     */
    protected static $dbFields = array();


    /**
     * Cached version of method: isDatabaseField, which is used for each getter and setter.
     * I tested this function to be on average 5 times faster than not cached version.
     *
     * @author Maciej
     *
     * @param $entityClass
     * @param $field
     *
     * @return bool
     * @deprecated assuming this is used for magic getters/setters, which should also be avoided
     */
    public static function isDatabaseField($entityClass, $field)
    {
        if(!isset(self::$dbFields[$entityClass])) {
            self::loadEntityMetadataReflfield($entityClass);
        }

        return (isset(self::$dbFields[$entityClass][$field]));
    }

    public static function loadEntityMetadataReflfield($entityClass)
    {
        $data = self::getEntityMetadata($entityClass);
        self::$dbFields[$entityClass] = $data->reflFields;
    }

    /**
     * This function is to immediately return an instance of the EntityManager,
     * without requiring you to be in the context of a specific Entity.
     *
     * @return \Doctrine\ORM\EntityManager
     * @deprecated EntityManager should only be accessed from Doctrine repository implementations
     */
    public static function getEntityManager()
    {
        return \Zend_Registry::get('doctrine')->getEntityManager();
    }


    /**
     * This function is used to tack on the namespace to the current entity
     * name if it's not already the full entity name.
     * Helpful to use in a few different places.
     *
     * @param $baseName
     * @deprecated Since entities may have multiple namespaces
     *
     * @return String containing the fully qualified classname, including its
     * namespace.
     */
    protected static function getFullEntityName($baseName)
    {

        if (strpos($baseName, "Fisdap\\Entity\\") === false) {
            return "\\Fisdap\\Entity\\" . $baseName;
        }

        return $baseName;
    }


    /**
     * This function will return an entity based on the provided name.  Useful
     * for things like Gender, Ethnicity, etc.
     *
     * @param String $entityName Name of the entity to search for and return.
     * @param String $name String containing the name to search for (ex: 'male')
     *
     * @return \Fisdap\Entity\EntityBaseClass of the requested type if a matching name
     * was found, or null if none found or the entity doesn't have a
     * name field.
     *
     * @deprecated
     */
    public static function getEntityByName($entityName, $name)
    {
        // First, check to see if the entity has a "name" field...
        $metadata = self::getEntityMetadata($entityName);

        if(array_key_exists('name', $metadata->fieldNames)){
            $entName = self::getFullEntityName($entityName);

            $dql = "SELECT e FROM $entName e WHERE e.name LIKE :name";

            $query = self::getEntityManager()->createQuery($dql);
            $query->setParameter('name', $name);

            $result = $query->getResult();

            if($result){
                return array_pop($result);
            }
        }

        return null;
    }

    /**
     * This function will return the repository for a given short entity name
     *
     * @param string $entityName Name of the entity
     *
     * @deprecated Repositories should be injected as dependencies or retrieved from a service container
     * @return \Fisdap\Data\Repository\Repository the repository for the given entity
     */
    public static function getRepository($entityName)
    {
        $em = self::getEntityManager();
        $fullEntityName = self::getFullEntityName($entityName);

        return $em->getRepository($fullEntityName);
    }


    // HELPER FUNCTIONS BELOW
    /**
     * Gets Entity Fields => Database Fields
     *
     * @param object|string  $entityOrClass
     * @param boolean $isClass = false,
     *                         if true, $entityOrClass is class
     *
     * @return array
     */
    public static function getEntityFields($entityOrClass, $isClass=false)
    {
        $class = ($isClass) ? $entityOrClass : get_class($entityOrClass);

        $metadata = self::getEntityManager()->getClassMetadata($class);

        // start with metadata fields
        $fields = ($metadata->fieldNames);

        self::entityAddMappedFields($fields, $metadata);

        return $fields;
    }


    /**
     *	Gets array(<database fields> => <doctrine fields>)
     *
     * @param object|string     $entityOrClass
     * @param bool $isClass
     *
     * @return array
     */
    public static function getEntityColumns($entityOrClass, $isClass=false)
    {
        $class = ($isClass) ? $entityOrClass : get_class($entityOrClass);

        $metadata = self::getEntityManager()->getClassMetadata($class);

        // start with metadata columns
        $fields = ($metadata->columnNames);

        self::entityAddMappedFields($fields, $metadata);

        return $fields;
    }


    /**
     *	Helper for getEntityFields and getEntityColumns
     *	Adds mapped fields to the list in metadata that excludes them.
     *
     *	@todo Column names for associationMappings fields are fieldNames for now.
     *		This will break if they have different names. (need example to figure it out)
     *
     * @param $fields
     * @param $metadata
     */
    public static function entityAddMappedFields(&$fields, $metadata)
    {
        // find association field that are stored separately
        foreach($metadata->associationMappings as $fieldName => $props) {
            if (isset($props['joinColumns'])) {
                foreach ($props['joinColumns'] as $cols) {
                    if (!isset($metadata->columnNames[$cols['name']])) {
                        $fields[$cols['name']] = $cols['name'];
                    }
                }
            }
        }
    }


    public static function getAllData($entity)
    {
        if(!class_exists($entity)){
            // Try to add on the namespace, see if that helps...
            $entity = self::getFullEntityName($entity);
        }

        $repo = self::getEntityManager()->getRepository($entity);
        return $repo->findAll();
    }


    /**
     * Simple method to get ALL DATA from all the entities of a particular class
     * Mostly useful for enumerated value entities
     * using Array hydration to try to make this not as crazy horrible.
     * @param $entity
     * @return array
     */
    public static function getAllDataArray($entity) {
        if(!class_exists($entity)){
            // Try to add on the namespace, see if that helps...
            $entity = self::getFullEntityName($entity);
        }

        $qb = self::getEntityManager()->createQueryBuilder();
        $qb->select('e')
            ->from($entity, 'e');

        return $qb->getQuery()->getArrayResult();
    }
}