<?php namespace Fisdap\Fractal;

use League\Fractal\TransformerAbstract;

/**
 * Enables transformation of association fields that are not "included" in the entity but exist on the entity due
 * to JOINs in the query.
 *
 * Useful for transforming Entities with many associations, when it is desired for the
 * association to appear as part of the entity.
 *
 * @package Fisdap\Fractal
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
abstract class Transformer extends TransformerAbstract
{
    /**
     * @var string[]
     */
    protected $includes = [];

    /**
     * @var string
     */
    protected static $dateTimeFormat = 'Y-m-d H:i:s';


    /**
     * @return string[]
     */
    public function getIncludes()
    {
        return $this->includes;
    }

    /**
     * @param string[] $includes
     */
    public function setIncludes(array $includes)
    {
        $this->includes = $includes;
    }


    /**
     * Shortcut method to remove array elements
     *
     * @param string[] $fields
     * @param array $entity
     */
    protected function removeFields(array $fields, array &$entity)
    {
        foreach ($fields as $field) {
            unset($entity[$field]);
        }
    }

    /**
     * Shortcut method to convert a \DateTime to a string formatted as 'Y-m-d H:i:s'
     *
     * @param \DateTime $dateTime
     *
     * @return string
     * @deprecated
     */
    protected function formatDateTimeAsYmdHis(\DateTime $dateTime)
    {
        return $dateTime->format(static::$dateTimeFormat);
    }


    /**
     * @param string $name
     * @param array  $entity
     *
     * @return int|null
     *
     */
    protected function getIdFromAssociation($name, array &$entity)
    {
        $id = null;

        // I don't think we want this includes exclusion, but commented
        // just in case we need to rollback this change:
        //
        // if (isset($entity[$name]) && ! in_array($name, $this->includes)) {
        if (isset($entity[$name])) {
            $association = $entity[$name];
            if (is_array($association)) {
                if (array_key_exists('id', $association)) {
                    $id = $association['id'];
                }
            } else {
                if (property_exists($association, 'id')) {
                    $id = $association->id;
                }
            }
        }

        return $id;
    }


    /**
     * @param string $name
     * @param array  $entity
     *
     * @return array
     */
    protected function getIdsFromAssociation($name, array &$entity)
    {
        $ids = [];

        // I don't think we want this includes exclusion, but commented
        // just in case we need to rollback this change:
        //
        // if (isset($entity[$name]) && ! in_array($name, $this->includes)) {
        if (isset($entity[$name])) {
            foreach ($entity[$name] as $association) {
                if (is_array($association)) {
                    if (array_key_exists('id', $association)) {
                        array_push($ids, $association['id']);
                    }
                } else {
                    if (property_exists($association, 'id')) {
                        array_push($ids, $association->id);
                    }
                }
            }
        }

        return $ids;
    }


    /**
     * @param array $transformedEntity
     * @param array $entity
     */
    protected function addCommonTimestamps(array &$transformedEntity, array &$entity)
    {
        $transformedEntity['created'] = $this->formatDateTimeAsString($entity['created']);
        $transformedEntity['updated'] = $this->formatDateTimeAsString($entity['updated']);
    }


    /**
     * Shortcut method to convert a \DateTime to a string
     *
     * @param \DateTime|null $dateTime
     *
     * @return string|null
     */
    protected function formatDateTimeAsString($dateTime)
    {
        return $dateTime instanceof \DateTime ? $dateTime->format(static::$dateTimeFormat) : null;
    }
}
