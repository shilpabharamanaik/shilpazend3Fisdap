<?php namespace Fisdap\Api\Queries\Parameters;


/**
 * Template for encapsulating common query parameters
 *
 * @package Fisdap\Api\Queries\Parameters
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
abstract class CommonQueryParameters
{
    /**
     * @var string[]|null
     */
    protected $associations = null;

    /**
     * @var string[]|null
     */
    protected $associationIds = null;

    /**
     * @var int|null
     */
    protected $firstResult = null;

    /**
     * @var int|null
     */
    protected $maxResults = null;


    /**
     * @return string[]
     * @codeCoverageIgnore
     */
    public function getAssociations()
    {
        return $this->associations;
    }


    /**
     * @param string[] $associations
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setAssociations(array $associations = null)
    {
        $this->associations = $associations;

        return $this;
    }


    /**
     * @return string[]
     * @codeCoverageIgnore
     */
    public function getAssociationIds()
    {
        return $this->associationIds;
    }


    /**
     * @param string[] $associationIds
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setAssociationIds(array $associationIds = null)
    {
        $this->associationIds = $associationIds;

        return $this;
    }


    /**
     * @return int|null
     * @codeCoverageIgnore
     */
    public function getFirstResult()
    {
        return $this->firstResult;
    }


    /**
     * @param int|null $firstResult
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setFirstResult($firstResult)
    {
        $this->firstResult = $firstResult;

        return $this;
    }


    /**
     * @return int|null
     * @codeCoverageIgnore
     */
    public function getMaxResults()
    {
        return $this->maxResults;
    }


    /**
     * @param int|null $maxResults
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setMaxResults($maxResults)
    {
        $this->maxResults = $maxResults;

        return $this;
    }
}