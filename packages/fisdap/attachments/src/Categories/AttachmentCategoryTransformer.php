<?php namespace Fisdap\Attachments\Categories;

use Fisdap\Attachments\Categories\Entity\AttachmentCategory;
use Fisdap\Fractal\Transformer;

/**
 * Prepares attachment category data for JSON output
 *
 * @package Fisdap\Attachments\Categories
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class AttachmentCategoryTransformer extends Transformer
{
    /**
     * @param AttachmentCategory|array $attachmentCategory
     *
     * @return array
     */
    public function transform($attachmentCategory)
    {
        if ($attachmentCategory instanceof AttachmentCategory) {
            $transformedAttachmentCategory = $attachmentCategory->toArray();
        } else {
            $transformedAttachmentCategory = $attachmentCategory;
        }

        // displaying the type would be redundant, so remove it
        unset($transformedAttachmentCategory['type']);

        return $transformedAttachmentCategory;
    }
}
