<?php namespace Fisdap\Attachments\Queries\Specifications;

use Happyr\DoctrineSpecification\BaseSpecification;
use Happyr\DoctrineSpecification\Spec;

/**
 * Class AttachmentById
 *
 * @package Fisdap\Attachments\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class AttachmentById extends BaseSpecification
{
    /**
     * @var string
     */
    private $attachmentEntityClassName;

    /**
     * @var string
     */
    private $id;


    /**
     * @param string $attachmentEntityClassName
     * @param string $id
     * @param null   $dqlAlias
     */
    public function __construct($attachmentEntityClassName, $id, $dqlAlias = null)
    {
        $this->attachmentEntityClassName = $attachmentEntityClassName;
        $this->id = $id;

        parent::__construct($dqlAlias);
    }


    public function getSpec()
    {
        return Spec::andX(
            new ByAttachmentType($this->attachmentEntityClassName),
            Spec::eq('id', $this->id, null, 'uuid')
        );
    }
}
