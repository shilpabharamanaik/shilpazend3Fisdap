<?php namespace Fisdap\Doctrine\Extensions\IdGenerator;

use Doctrine\ORM\Id\AbstractIdGenerator;
use Doctrine\ORM\EntityManager;
use Fisdap\Doctrine\Extensions\ColumnType\UuidType;


/**
 * An ID generation strategy for Doctrine that complements the UuidType column type.
 *
 * Put this to use with your uuid-type doctrine columns by using annotation like this:
 *
 *  @Id
 *  @Column(type="uuid")
 *  @GeneratedValue(strategy="CUSTOM")
 *  @CustomIdGenerator(class="Fisdap\Doctrine\Extensions\IdGenerator\UuidGenerator")
 *
 * Please note: you cannot use @GeneratedValue twice in one entity. Only use on the primary column.
 * So this is not useful in interim situations where there is both a traditiona ID and a UUID
 *
 * for info, see http://stackoverflow.com/questions/9087585/generating-next-sequence-value-manually-in-doctrine-2/28561017#28561017
 *
 * @package Fisdap\Doctrine\Extensions\IdGenerator
 * @author  Jesse Mortenson <jmortenson@fisdap.net>
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class UuidGenerator extends AbstractIdGenerator
{
    /**
     * @inheritdoc
     */
    public function generate(EntityManager $em, $entity)
    {
        // get value for id
        $entityMetadata = $em->getClassMetadata(get_class($entity));
        $id = $entityMetadata->getSingleIdReflectionProperty()->getValue($entity);

        // only generate a UUID if one doesn't already exist on the entity
        if ($id !== null) {
            return $id;
        }

        return UuidType::generateUuid();
    }
}