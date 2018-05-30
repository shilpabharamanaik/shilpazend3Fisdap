<?php namespace Fisdap\Api\Client\Gateway;

use Fisdap\Api\Client\Exceptions\MissingUriRoot;
use Fisdap\Api\Client\HttpClient\HttpClientInterface;
use Fisdap\Api\Client\JsonMapper;

/**
 * Template for an HTTP implementation of a Gateway
 *
 * @package Fisdap\Api\Client\Gateway
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
abstract class CommonHttpGateway implements Gateway
{
    /**
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * @var JsonMapper
     */
    protected $mapper;

    /**
     * @var string
     */
    protected $responseType = self::RESPONSE_TYPE_OBJECT;

    /**
     * @var string
     */
    protected static $uriRoot = null;


    /**
     * @param HttpClientInterface $client
     * @param JsonMapper          $mapper
     *
     * @throws MissingUriRoot
     */
    public function __construct(HttpClientInterface $client, JsonMapper $mapper)
    {
        if (static::$uriRoot === null) {
            throw new MissingUriRoot(
                'Classes extending ' . __CLASS__ . " must set a value for the 'uriRoot' protected static property."
            );
        }

        $this->client = $client;
        $this->mapper = $mapper;
    }


    /**
     * @inheritdoc
     */
    public function setResponseType($responseType)
    {
        $this->responseType = $responseType;

        return $this;
    }


    /**
     * @inheritdoc
     */
    public function getResponseType()
    {
        return $this->responseType;
    }
}
