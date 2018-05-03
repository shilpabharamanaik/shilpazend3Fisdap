<?php namespace Fisdap\Attachments\Categories\Repository;

use Fisdap\Data\Repository\DoctrineRepository;

/**
 * Doctrine implementation of attachment categories repository
 *
 * @package Fisdap\Attachments\Categories\Repository
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class DoctrineAttachmentCategoriesRepository extends DoctrineRepository implements AttachmentCategoriesRepository
{
    /**
     * @inheritdoc
     */
    public function getOneByNameAndType($name, $attachmentCategoryEntityClassName)
    {
        $queryBuilder = $this->createQueryBuilder('attachmentCategory');
        $queryBuilder->select('attachmentCategory')
            ->where("attachmentCategory.name = :name")
            ->andWhere("attachmentCategory INSTANCE OF $attachmentCategoryEntityClassName")
            ->setParameter('name', $name);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }


    /**
     * @param array  $names
     * @param string $attachmentCategoryEntityClassName
     *
     * @return mixed
     */
    public function getByNameAndType(array $names, $attachmentCategoryEntityClassName)
    {
        $queryBuilder = $this->createQueryBuilder('attachmentCategory');
        $queryBuilder->select('attachmentCategory')
            ->where($queryBuilder->expr()->in('attachmentCategory.name', ':names'))
            ->andWhere("attachmentCategory INSTANCE OF $attachmentCategoryEntityClassName")
            ->setParameter('names', $names)
            ->orderBy('attachmentCategory.name', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }


    /**
     * @inheritdoc
     */
    public function getAllByType($attachmentCategoryEntityClassName, $asArray = true)
    {
        $queryBuilder = $this->createQueryBuilder('attachmentCategory');
        $queryBuilder->select('attachmentCategory')
            ->where("attachmentCategory INSTANCE OF $attachmentCategoryEntityClassName")
            ->orderBy('attachmentCategory.name', 'ASC');

        if ($asArray === false) {
            return $queryBuilder->getQuery()->getResult();
        }

        return $queryBuilder->getQuery()->getArrayResult();
    }
}
