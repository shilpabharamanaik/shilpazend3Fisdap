<?php namespace Fisdap\Attachments\Associations;

use Fisdap\Attachments\Configuration\AttachmentConfig;
use Fisdap\Attachments\Core\Kernel\AttachmentsKernel;
use Fisdap\Attachments\Core\MissingAttachmentConfiguration;
use Illuminate\Contracts\Container\Container;

/**
 * Service for handling attachment operations on associate entities
 *
 * @package Fisdap\Attachments\Associations
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class AssociatedEntityFinder implements FindsAssociatedEntities
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var AttachmentsKernel
     */
    private $attachmentsKernel;


    /**
     * @param Container                  $container
     * @param AttachmentsKernel $attachmentsKernel
     */
    public function __construct(Container $container, AttachmentsKernel $attachmentsKernel)
    {
        $this->container = $container;
        $this->attachmentsKernel = $attachmentsKernel;
    }


    /**
     * @inheritdoc
     */
    public function find($attachmentType, $associatedEntityId)
    {
        $associatedEntityRepository = $this->getAssociatedEntityRepository($attachmentType);

        $associatedEntity = $associatedEntityRepository->findAssociatedEntity($attachmentType, $associatedEntityId);

        return $associatedEntity;
    }


    /**
     * @inheritdoc
     */
    public function getAssociatedEntityRepository($attachmentType)
    {
        $attachmentConfig = $this->getAttachmentConfig($attachmentType);
        return $this->container->make($attachmentConfig->associatedEntityRepositoryInterfaceName);
    }


    /**
     * @param $attachmentType
     *
     * @return AttachmentConfig|null
     * @throws MissingAttachmentConfiguration
     */
    private function getAttachmentConfig($attachmentType)
    {
        return $this->attachmentsKernel->getAttachmentConfig($attachmentType);
    }
}
