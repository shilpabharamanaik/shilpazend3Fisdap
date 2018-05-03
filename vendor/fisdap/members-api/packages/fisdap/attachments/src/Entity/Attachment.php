<?php namespace Fisdap\Attachments\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\Table;
use Fisdap\Attachments\Associations\Entities\HasAttachments;
use Fisdap\Attachments\Categories\Entity\AttachmentCategory;
use Fisdap\Doctrine\Extensions\ColumnType\UuidType;
use Fisdap\Timestamps\EntityTimestampsSupport;
use Fisdap\Timestamps\HasTimestamps;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Attachment entity and template
 *
 * @package Fisdap\Attachments\Entity
 * @author  Ben Getsug <bgetsug@fisdap.net>
 *
 * @Entity(repositoryClass="Fisdap\Attachments\Repository\DoctrineAttachmentsRepository")
 * @Table(name="Attachments")
 * @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="attachmentType", type="string")
 * @HasLifecycleCallbacks
 */
class Attachment implements HasTimestamps
{
    use EntityTimestampsSupport;


    /**
     * @Id
     * @var string UUID
     * @Column(type="uuid", length=16)
     * @GeneratedValue(strategy="CUSTOM")
     * @CustomIdGenerator(class="Fisdap\Doctrine\Extensions\IdGenerator\UuidGenerator")
     */
    protected $id;

    /**
     * @var int
     * @Column(type="integer")
     */
    protected $userContextId;

    /**
     * @var HasAttachments
     */
    protected $associatedEntity;

    /**
     * @var string
     * @Column(type="string", length=128)
     */
    protected $fileName;

    /**
     * @var int File size in bytes
     * @Column(type="integer")
     */
    protected $size;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $mimeType;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $savePath;

    /**
     * @var bool
     * @Column(type="boolean")
     */
    protected $processed = false;

    /**
     * @var array
     * @Column(type="array", nullable=true)
     */
    protected $variationFileNames = null;

    /**
     * @var string
     * @Column(type="string", length=128, nullable=true)
     */
    protected $nickname;

    /**
     * @var string
     * @Column(type="text", nullable=true)
     */
    protected $notes;

    /**
     * @var AttachmentCategory[]|ArrayCollection
     */
    protected $categories;


    /**
     * @param int            $userContextId
     * @param HasAttachments $associatedEntity
     * @param string         $fileName
     * @param int            $size
     * @param string         $mimeType
     * @param string         $savePath
     * @param string|null    $id
     * @param string|null    $nickname
     * @param string|null    $notes
     */
    public function __construct(
        $userContextId,
        HasAttachments $associatedEntity,
        $fileName,
        $size,
        $mimeType,
        $savePath,
        $id = null,
        $nickname = null,
        $notes = null
    ) {
        $this->userContextId = $userContextId;
        $this->associatedEntity = $associatedEntity;
        $this->fileName = $fileName;
        $this->size = $size;
        $this->mimeType = $mimeType;
        $this->savePath = $savePath;
        $this->id = $id;
        $this->nickname = $nickname;
        $this->notes = $notes;
        $this->categories = new ArrayCollection();
    }


    /**
     * @return string
     */
    public static function generateId()
    {
        return UuidType::generateUuid();
    }


    /**
     * @param int            $userContextId
     * @param HasAttachments $associatedEntity
     * @param File           $attachmentFile
     * @param string         $savePath
     * @param string|null    $id
     * @param string|null    $nickname
     * @param string|null    $notes
     *
     * @return Attachment
     */
    public static function createFromFile(
        $userContextId,
        HasAttachments $associatedEntity,
        File $attachmentFile,
        $savePath,
        $id = null,
        $nickname = null,
        $notes = null
    ) {
        $attachment = new static(
            $userContextId,
            $associatedEntity,
            $attachmentFile->getFilename(),
            $attachmentFile->getSize(),
            $attachmentFile->getMimeType(),
            $savePath,
            $id,
            $nickname,
            $notes
        );

        return $attachment;
    }


    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return int
     */
    public function getUserContextId()
    {
        return $this->userContextId;
    }


    /**
     * @return HasAttachments
     */
    public function getAssociatedEntity()
    {
        return $this->associatedEntity;
    }


    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }


    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }


    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }


    /**
     * @return string
     */
    public function getSavePath()
    {
        return $this->savePath;
    }


    /**
     * @return boolean
     */
    public function isProcessed()
    {
        return $this->processed;
    }


    /**
     * @param boolean $processed
     */
    public function setProcessed($processed)
    {
        $this->processed = $processed;
    }


    /**
     * @return string[]
     */
    public function getVariationFileNames()
    {
        return $this->variationFileNames;
    }


    /**
     * @param string[] $variationFileNames
     */
    public function setVariationFileNames(array $variationFileNames)
    {
        $this->variationFileNames = $variationFileNames;
    }


    /**
     * @param string $variationName
     * @param string $fileName
     */
    public function addVariationFileName($variationName, $fileName)
    {
        $this->variationFileNames[$variationName] = $fileName;
    }


    /**
     * @param string $fileName
     */
    public function removeVariationFileName($fileName)
    {
        unset($this->variationFileNames[$fileName]);

        // compensate for unset() not removing array indexes
        $this->variationFileNames = array_values($this->variationFileNames);
    }


    /**
     * @return string
     */
    public function getNickname()
    {
        return $this->nickname;
    }


    /**
     * @param string $nickname
     */
    public function setNickname($nickname)
    {
        $this->nickname = $nickname;
    }


    /**
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }


    /**
     * @param string $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
    }


    /**
     * @return ArrayCollection|AttachmentCategory[]
     */
    public function getCategories()
    {
        return $this->categories;
    }


    /**
     * @param AttachmentCategory[] $categories
     */
    public function setCategories(array $categories)
    {
        $this->categories = new ArrayCollection();

        foreach ($categories as $category) {
            $this->addCategory($category);
        }
    }


    /**
     * @param AttachmentCategory $attachmentCategory
     */
    public function addCategory(AttachmentCategory $attachmentCategory)
    {
        $this->getCategories()->add($attachmentCategory);
    }


    /**
     * @param AttachmentCategory $attachmentCategory
     */
    public function removeCategory(AttachmentCategory $attachmentCategory)
    {
        $this->getCategories()->removeElement($attachmentCategory);
    }


    /**
     * Remove all categories
     *
     * NOTE: ArrayCollection::clear() doesn't work, due to issues with ManyToManyPersister::getDeleteSQL()
     * not supporting custom types. As a workaround, we loop through the collection and remove each individually.
     *
     * @see http://www.doctrine-project.org/jira/browse/DDC-2531
     */
    public function clearCategories()
    {
        /*
         * Doesn't work due to DDC-2531
         */
        //$this->getCategories()->clear();

        foreach ($this->getCategories() as $attachmentCategory) {
            $this->removeCategory($attachmentCategory);
        }
    }


    /*
     * Additional helper getters
     */

    /**
     * @return string
     */
    public function getExtension()
    {
        return pathinfo($this->fileName, PATHINFO_EXTENSION);
    }


    /**
     * @return string
     */
    public function getFileNameWithoutExtension()
    {
        return pathinfo($this->fileName, PATHINFO_FILENAME);
    }


    /**
     * @return array
     */
    public function toArray()
    {
        $categories = [];

        foreach ($this->getCategories() as $category) {
            array_push($categories, $category->toArray());
        }

        return [
            'id'                 => $this->getId(),
            'userContextId'      => $this->getUserContextId(),
            'associatedEntityId' => $this->getAssociatedEntity() != null ? $this->getAssociatedEntity()->getId() : null,
            'fileName'           => $this->getFileName(),
            'size'               => $this->getSize(),
            'mimeType'           => $this->getMimeType(),
            'savePath'           => $this->getSavePath(),
            'processed'          => $this->isProcessed(),
            'variationFileNames' => $this->getVariationFileNames(),
            'nickname'           => $this->getNickname(),
            'notes'              => $this->getNotes(),
            'categories'         => count($categories) > 0 ? $categories : null,
            'created'            => $this->getCreated(),
            'updated'            => $this->getUpdated()
        ];
    }
}
