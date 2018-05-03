<?php namespace Fisdap\Attachments\Queries\Specifications;

use Happyr\DoctrineSpecification\BaseSpecification;
use Happyr\DoctrineSpecification\Spec;

/**
 * Class AttachmentsByAssociatedEntityId
 *
 * @package Fisdap\Attachments\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class AttachmentsByAssociatedEntityId extends BaseSpecification
{
    /**
     * @var string
     */
    private $attachmentEntityClassName;

    /**
     * @var int
     */
    private $associatedEntityId;


    /**
     * @param string $attachmentEntityClassName
     * @param int    $associatedEntityId
     * @param null   $dqlAlias
     */
    public function __construct($attachmentEntityClassName, $associatedEntityId, $dqlAlias = null)
    {
        parent::__construct($dqlAlias);
        $this->attachmentEntityClassName = $attachmentEntityClassName;
        $this->associatedEntityId = $associatedEntityId;
    }


    /**
     * @return \Happyr\DoctrineSpecification\Logic\AndX
     */
    public function getSpec()
    {
        return  Spec::andX(
            new ByAttachmentType($this->attachmentEntityClassName),
            Spec::eq('associatedEntity', $this->associatedEntityId)
        );
    }
}
