<?php namespace Fisdap\Attachments\Repository;

use Fisdap\Attachments\Entity\Attachment;
use Fisdap\Data\Repository\DoctrineRepository;

/**
 * Doctrine implementation of attachments repository
 *
 * @package Fisdap\Attachments\Repository
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class DoctrineAttachmentsRepository extends DoctrineRepository implements AttachmentsRepository
{
    /**
     * @var string
     */
    private $attachmentEntityClassName = null;


    /**
     * @inheritdoc
     */
    public function getOneById($id)
    {
        if ($this->attachmentEntityClassName === null) {
            throw new \BadMethodCallException(__CLASS__ . '::$attachmentEntityClassName must be set');
        }

        $queryBuilder = $this->createQueryBuilder('attachment');
        $queryBuilder->where("attachment INSTANCE OF {$this->attachmentEntityClassName}")
            ->andWhere('attachment.id = :id')
            ->setParameter('id', $id, 'uuid');

        if ($this->logger !== null) {
            $this->logger->debug(
                'Doctrine DQL: ' . $queryBuilder->getDQL(),
                $this->getDebugContext()
            );
        }

        $result = $queryBuilder->getQuery()->getResult();

        return ! empty($result) ? $result[0] : null;
    }


    public function getById(array $ids)
    {
        throw new \Exception('getById() is not supported for attachments. Please use the AttachmentsFinder.');
    }


    /**
     * @param string $attachmentEntityClassName
     *
     * @return $this
     */
    public function setAttachmentEntityClassName($attachmentEntityClassName)
    {
        $this->attachmentEntityClassName = $attachmentEntityClassName;

        return $this;
    }


    /**
     * @inheritdoc
     */
    public function getCountByUserContextId($userContextId, array $attachmentEntityClassNames = null)
    {
        $queryBuilder = $this->createQueryBuilder('attachment');
        $queryBuilder->select('COUNT(attachment.id)')
            ->where('attachment.userContextId = :userContextId')
            ->setParameter('userContextId', $userContextId);

        if ($attachmentEntityClassNames !== null) {
            $whereClause = "attachment INSTANCE OF ";

            for ($i = 0; $i < count($attachmentEntityClassNames); ++$i) {
                if ($i == 0) {
                    $whereClause .= "$attachmentEntityClassNames[$i]";
                } else {
                    $whereClause .= " OR attachment INSTANCE OF $attachmentEntityClassNames[$i]";
                }
            }

            $queryBuilder->andWhere($whereClause);
        }

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }


    /**
     * @param Attachment $entity
     *
     * @return mixed
     */
    public function destroy($entity)
    {
        $queryBuilder = $this->createQueryBuilder('e');
        $queryBuilder->delete()
            ->where("e.id = :id")
            ->setParameter('id', $entity->getId(), 'uuid');
        return $queryBuilder->getQuery()->execute();
    }


    /**
     * @param array $ids
     *
     * @return void
     * @throws \Exception
     */
    public function destroyCollection(array $ids)
    {
        throw new \Exception(
            'destroyCollection() is not supported for attachments. Please use the DeleteAttachmentsCommand'
        );
    }
}
