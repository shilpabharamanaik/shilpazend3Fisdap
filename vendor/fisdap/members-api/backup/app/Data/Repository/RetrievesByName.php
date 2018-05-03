<?php namespace Fisdap\Data\Repository;

/**
 * Trait RetrievesByName
 *
 * @package Fisdap\Data\Repository
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait RetrievesByName
{
    /**
     * @inheritdoc
     */
    public function getOneByName($name)
    {
        return $this->findOneBy(['name' => $name]);
    }
}