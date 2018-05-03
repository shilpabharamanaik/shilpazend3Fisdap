<?php namespace Fisdap\Attachments\Associations\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Fisdap\Attachments\Entity\Attachment;

/**
 * Trait for implementing HasAttachments contract
 *
 * @package Fisdap\Attachments\Associations\Entities
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait EntityAttachmentsSupport
{
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return ArrayCollection
     */
    public function getAttachments()
    {
        return $this->attachments;
    }


    /**
     * @param string $attachmentId
     *
     * @return Attachment
     */
    public function getAttachmentById($attachmentId)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("id", $attachmentId))
            ->setFirstResult(0)
            ->setMaxResults(1)
        ;

        return $this->getAttachments()->matching($criteria)[0];
    }


    /**
     * @param Attachment $attachment
     */
    public function addAttachment(Attachment $attachment)
    {
        $this->getAttachments()->add($attachment);
    }


    /**
     * @param Attachment $attachment
     */
    public function removeAttachment(Attachment $attachment)
    {
        $this->getAttachments()->removeElement($attachment);
    }
}
