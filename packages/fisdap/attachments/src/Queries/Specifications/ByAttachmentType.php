<?php namespace Fisdap\Attachments\Queries\Specifications;

use Doctrine\ORM\QueryBuilder;
use Happyr\DoctrineSpecification\Query\QueryModifier;

/**
 * Class ByAttachmentType
 *
 * @package Fisdap\Attachments\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class ByAttachmentType implements QueryModifier
{
    /**
     * @var string
     */
    private $attachmentEntityClassName;


    /**
     * @param string $attachmentEntityClassName
     */
    public function __construct($attachmentEntityClassName)
    {
        $this->attachmentEntityClassName = $attachmentEntityClassName;
    }


    /**
     * @param QueryBuilder $qb
     * @param string       $dqlAlias
     *
     * @throws \Exception
     */
    public function modify(QueryBuilder $qb, $dqlAlias)
    {
        $qb->resetDQLParts(['from', 'select']);
        $qb->select($dqlAlias, 'categories')
            ->from($this->attachmentEntityClassName, $dqlAlias)
            ->leftJoin("$dqlAlias.categories", 'categories')
            ->orderBy("$dqlAlias.nickname")->addOrderBy("$dqlAlias.fileName");
    }
}
