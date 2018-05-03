<?php namespace Fisdap\Attachments\Cdn;

use Fisdap\Attachments\Core\Kernel\AttachmentsKernel;

/**
 * Template for signed URL generation
 *
 * @package Fisdap\Attachments\Cdn
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
abstract class SignedUrlGenerator implements GeneratesSignedUrls
{
    /**
     * @var AttachmentsKernel
     */
    protected $attachmentsKernel;


    /**
     * @param AttachmentsKernel $attachmentsKernel
     */
    public function __construct(AttachmentsKernel $attachmentsKernel)
    {
        $this->attachmentsKernel = $attachmentsKernel;
    }
}
