<?php namespace Fisdap\Attachments\Http;

use Fisdap\Attachments\Core\Kernel\AttachmentsKernel;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class MimeTypeBlacklistValidator
 *
 * @package Fisdap\Attachments\Http
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class MimeTypeBlacklistValidator
{
    /**
     * @param AttachmentsKernel $attachmentsKernel
     */
    public function __construct(AttachmentsKernel $attachmentsKernel)
    {
        $this->attachmentsKernel = $attachmentsKernel;
    }


    /**
     * @param string $attribute
     * @param mixed $value
     * @param mixed $parameters
     *
     * @return bool
     */
    public function validate($attribute, $value, $parameters)
    {
        if ($value instanceof UploadedFile && !$value->isValid()) {
            return false;
        }

        $mimeType = $value->getClientMimeType();
        
        $mimeTypesBlacklist = $this->attachmentsKernel->getMimeTypesBlacklist();

        return ! in_array($mimeType, $mimeTypesBlacklist);
    }
}
