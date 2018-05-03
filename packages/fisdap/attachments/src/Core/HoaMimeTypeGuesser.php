<?php namespace Fisdap\Attachments\Core;

use Hoa\File\Read;
use Hoa\Mime\Mime;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;

/**
 * Determines mime types using the hoa/mime library
 *
 * @see https://github.com/hoaproject/Mime
 *
 * @package Fisdap\Attachments\Core
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class HoaMimeTypeGuesser implements MimeTypeGuesserInterface
{
    /**
     * @inheritdoc
     */
    public function guess($path)
    {
        if (!is_file($path)) {
            throw new FileNotFoundException($path);
        }

        if (!is_readable($path)) {
            throw new AccessDeniedException($path);
        }

        return (new Mime(new Read($path)))->getMime();
    }
}
