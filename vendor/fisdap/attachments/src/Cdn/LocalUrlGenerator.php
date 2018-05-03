<?php namespace Fisdap\Attachments\Cdn;

/**
 * Pass-through URL generator for testing purposes
 *
 * @package Fisdap\Attachments\Cdn
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class LocalUrlGenerator extends SignedUrlGenerator
{
    /**
     * @param string $unsignedUrl
     *
     * @return string
     */
    public function generate($unsignedUrl)
    {
        return $unsignedUrl;
    }
}
