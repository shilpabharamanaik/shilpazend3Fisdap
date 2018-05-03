<?php namespace Fisdap\Attachments\Cdn;

use Illuminate\Contracts\Container\Container;

/**
 * Creates a SignedUrlGenerator according to specified CDN name, resolving appropriate class from service container
 *
 * @package Fisdap\Attachments\Cdn
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class SignedUrlGeneratorFactory
{
    /**
     * @var Container
     */
    private $container;


    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }


    /**
     * @param $cdnName
     *
     * @return GeneratesSignedUrls
     * @throws UnsupportedCdn
     */
    public function create($cdnName)
    {
        switch ($cdnName) {
            case 'cloudfront':
                return $this->container->make(CloudFrontSignedUrlGenerator::class);
                break;
            case 'local':
                return $this->container->make(LocalUrlGenerator::class);
                break;
            default:
                throw new UnsupportedCdn("No SignedUrlGeneratorFactory available for the '$cdnName' CDN");
                break;
        }
    }
}
