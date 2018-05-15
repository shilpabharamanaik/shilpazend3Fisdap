<?php namespace Fisdap\Api\Support;

use Illuminate\Http\Request;
use JsonMapper;

/**
 * Uses JsonMapper to map the body of a Request containing JSON to the properties of an object
 *
 * @package Fisdap\Api\Support
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class JsonRequestMapper
{
    /**
     * @var JsonMapper
     */
    private $jsonMapper;

    /**
     * @var Request
     */
    private $request;


    /**
     * JsonRequestMapper constructor.
     *
     * @param JsonMapper $jsonMapper
     * @param Request    $request
     */
    public function __construct(JsonMapper $jsonMapper, Request $request)
    {
        // allow mapping of arrays
        $jsonMapper->bEnforceMapType = false;

        $this->jsonMapper = $jsonMapper;
        $this->request = $request;
    }


    /**
     * @param object $object
     *
     * @return object
     */
    public function map($object)
    {
        return $this->jsonMapper->map($this->request->json(), $object);
    }
}
