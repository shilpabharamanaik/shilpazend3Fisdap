<?php namespace Fisdap\Queries\Specifications;


/**
 * Class Partial
 *
 * @package Fisdap\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class Partial
{
    /**
     * @var string[]
     */
    public $fields;

    /**
     * @var string|null
     */
    public $newAlias;

    /**
     * @var string|null
     */
    public $dqlAlias;


    /**
     * @param string[]      $fields
     * @param string|null   $newAlias
     * @param string|null   $dqlAlias
     */
    public function __construct(array $fields, $newAlias = null, $dqlAlias = null)
    {
        $this->fields = $fields;
        $this->newAlias = $newAlias;
        $this->dqlAlias = $dqlAlias;
    }
}