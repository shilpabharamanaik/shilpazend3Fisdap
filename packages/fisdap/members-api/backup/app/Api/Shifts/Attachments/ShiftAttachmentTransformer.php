<?php namespace Fisdap\Api\Shifts\Attachments;

use Fisdap\Api\Shifts\PreceptorSignoff\VerificationTransformer;
use Fisdap\Attachments\Entity\Attachment;
use Fisdap\Attachments\Transformation\TransformsAttachments;
use Fisdap\Fractal\Transformer;

/**
 * AttachmentTransformer decorator for shift attachments
 *
 * @package Fisdap\Api\Shifts\Attachments
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ShiftAttachmentTransformer extends Transformer implements TransformsAttachments
{
    /**
     * @var string[]
     */
    protected $availableIncludes = [
        'verifications'
    ];

    /**
     * @var TransformsAttachments
     */
    private $attachmentTransformer;


    /**
     * @param TransformsAttachments $attachmentTransformer
     */
    public function __construct(TransformsAttachments $attachmentTransformer)
    {
        $this->attachmentTransformer = $attachmentTransformer;
    }


    /**
     * @param string $attachmentType
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setAttachmentType($attachmentType)
    {
        $this->attachmentTransformer->setAttachmentType($attachmentType);
    }


    /**
     * @param Attachment|array $attachment
     *
     * @return array
     */
    public function transform($attachment)
    {
        $transformedAttachment = $this->attachmentTransformer->transform($attachment);

        $transformedAttachment['verification_ids'] = null;

        if (isset($transformedAttachment['verifications']) && ! in_array('verifications', $this->includes)) {
            foreach ($transformedAttachment['verifications'] as $verification) {
                $transformedAttachment['verification_ids'][] = $verification['id'];
            }

            unset($transformedAttachment['verifications']);
        }

        return $transformedAttachment;
    }


    /**
     * @param array $shiftAttachment
     *
     * @return \League\Fractal\Resource\Item
     * @codeCoverageIgnore
     */
    public function includeVerifications(array $shiftAttachment)
    {
        $verifications = $shiftAttachment['verifications'];

        return $this->collection($verifications, new VerificationTransformer);
    }
}
