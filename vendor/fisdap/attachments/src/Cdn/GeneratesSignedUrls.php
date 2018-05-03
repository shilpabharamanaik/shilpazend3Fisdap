<?php namespace Fisdap\Attachments\Cdn;

/**
 * Contract for signed URL generation
 *
 * @package Fisdap\Attachments\Cdn
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface GeneratesSignedUrls
{
    /**
     * @param string $unsignedUrl
     *
     * @return string
     */
    public function generate($unsignedUrl);
}
