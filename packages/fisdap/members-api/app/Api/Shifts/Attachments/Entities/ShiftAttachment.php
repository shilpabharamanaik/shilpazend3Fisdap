<?php namespace Fisdap\Api\Shifts\Attachments\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Fisdap\Attachments\Associations\Entities\HasAttachments;
use Fisdap\Attachments\Configuration\AttachmentConfig;
use Fisdap\Attachments\Configuration\ImageAttachmentVariationConfig;
use Fisdap\Attachments\Entity\Attachment;
use Fisdap\Entity\ShiftLegacy;
use Fisdap\Entity\Verification;

/**
 * Shift attachment Entity
 *
 * @package Fisdap\Api\Shifts\Attachments
 * @author  Ben Getsug <bgetsug@fisdap.net>
 *
 * @Entity
 * @Table(name="ShiftAttachments")
 *
 * @AttachmentConfig(
 *  associatedEntityRepositoryInterfaceName="Fisdap\Data\Shift\ShiftLegacyRepository",
 *  transformerClassName="Fisdap\Api\Shifts\Attachments\ShiftAttachmentTransformer",
 *  variationConfigurations={
 *      @ImageAttachmentVariationConfig(
 *          name="thumbnail",
 *          imageProcessorFilterClassName="Fisdap\Attachments\Processing\ImageFilters\ThumbnailFilter"
 *      ),
 *      @ImageAttachmentVariationConfig(
 *          name="medium",
 *          imageProcessorFilterClassName="Fisdap\Attachments\Processing\ImageFilters\MediumFilter"
 *      )
 *  }
 * )
 */
class ShiftAttachment extends Attachment
{
    /**
     * @var ShiftLegacy
     * @ManyToOne(targetEntity="Fisdap\Entity\ShiftLegacy", inversedBy="attachments")
     * @JoinColumn(name="shiftId", referencedColumnName="Shift_id")
     */
    protected $associatedEntity;

    /**
     * @var ShiftAttachmentCategory[]|ArrayCollection
     * @ManyToMany(targetEntity="Fisdap\Api\Shifts\Attachments\Entities\ShiftAttachmentCategory",
     *  fetch="EAGER", cascade={"persist"}
     * )
     * @JoinTable(name="ShiftAttachmentCategories",
     *  joinColumns={@JoinColumn(name="ShiftAttachmentId", referencedColumnName="id", onDelete="CASCADE")},
     *  inverseJoinColumns={@JoinColumn(name="ShiftAttachmentCategoryId", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $categories;

    /**
     * @var ArrayCollection|Verification[]
     * @OneToMany(targetEntity="Fisdap\Entity\Verification", mappedBy="shiftAttachment")
     */
    protected $verifications;


    /**
     * @inheritdoc
     */
    public function __construct(
        $userContextId,
        HasAttachments $associatedEntity,
        $fileName,
        $size,
        $mimeType,
        $urlRoot,
        $id = null,
        $nickname = null,
        $notes = null
    ) {
        parent::__construct(
            $userContextId,
            $associatedEntity,
            $fileName,
            $size,
            $mimeType,
            $urlRoot,
            $id,
            $nickname,
            $notes
        );
        $this->verifications = new ArrayCollection();
    }


    /**
     * @return ArrayCollection|Verification[]
     * @codeCoverageIgnore
     */
    public function getVerifications()
    {
        return $this->verifications;
    }


    /**
     * @param Verification $verification
     * @codeCoverageIgnore
     */
    public function addVerification(Verification $verification)
    {
        $this->verifications->add($verification);
    }


    /**
     * @param Verification $verification
     * @codeCoverageIgnore
     */
    public function removeVerification(Verification $verification)
    {
        $this->verifications->removeElement($verification);
    }


    /**
     * @param Verification[] $verifications
     * @codeCoverageIgnore
     */
    public function removeVerifications(array $verifications)
    {
        foreach ($verifications as $verification) {
            $this->removeVerification($verification);
        }
    }


    /**
     * Remove all Verifications
     *
     * NOTE: this may not work due to issues with Doctrine and custom types
     * @codeCoverageIgnore
     */
    public function clearVerifications()
    {
        $this->verifications->clear();
    }


    public function toArray()
    {
        /*
        $attachmentProperties = parent::toArray();

        if ( ! $this->verifications->isEmpty()) {
            foreach ($this->verifications as $verification) {
                $attachmentProperties['verifications'][] = $verification->toArray();
            }
        }

        return $attachmentProperties;
        */

        return parent::toArray();
    }
}
