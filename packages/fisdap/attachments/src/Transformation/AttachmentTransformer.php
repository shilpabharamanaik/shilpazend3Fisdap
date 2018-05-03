<?php namespace Fisdap\Attachments\Transformation;

use Fisdap\Attachments\Cdn;
use Fisdap\Attachments\Cdn\SignedUrlGeneratorFactory;
use Fisdap\Attachments\Core\Kernel\AttachmentsKernel;
use Fisdap\Attachments\Entity\Attachment;
use Fisdap\Fractal\Transformer;

/**
 * Prepares attachment data for JSON output, adding signed URLs as needed
 *
 * @package Fisdap\Attachments
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class AttachmentTransformer extends Transformer implements TransformsAttachments
{
    /**
     * @var AttachmentsKernel
     */
    private $attachmentsKernel;

    /**
     * @var SignedUrlGeneratorFactory
     */
    private $signedUrlGeneratorFactory;

    /**
     * @var string
     */
    private $attachmentType;


    /**
     * @param AttachmentsKernel $attachmentsKernel
     * @param SignedUrlGeneratorFactory  $signedUrlGeneratorFactory
     */
    public function __construct(
        AttachmentsKernel $attachmentsKernel,
        SignedUrlGeneratorFactory $signedUrlGeneratorFactory
    ) {
        $this->attachmentsKernel = $attachmentsKernel;
        $this->signedUrlGeneratorFactory = $signedUrlGeneratorFactory;
    }


    /**
     * @inheritdoc
     */
    public function setAttachmentType($attachmentType)
    {
        $this->attachmentType = $attachmentType;

        return $this;
    }


    /**
     * @inheritdoc
     */
    public function transform($attachment)
    {
        if ($attachment instanceof Attachment) {
            $transformedAttachment = $attachment->toArray();
        } else {
            $transformedAttachment = $attachment;
        }

        $this->transformFields($transformedAttachment);

        return $transformedAttachment;
    }


    /**
     * @param $transformedAttachment
     */
    private function transformFields(&$transformedAttachment)
    {
        // remove attachmentType as it's only used internally
        unset($transformedAttachment['attachmentType']);

        foreach ($transformedAttachment as $key => $value) {
            switch ($key) {
                case 'categories':
                    $transformedAttachment['categories'] = count($value) > 0 ? array_pluck($value, 'name') : null;
                    break;

                case 'processed':
                    if ($value === true) {
                        $this->generateSignedCdnUrls($transformedAttachment);
                    } else {
                        $this->generateTempUrl($transformedAttachment);
                    }
                    break;

                case 'created':
                case 'updated':
                    $transformedAttachment[$key] = isset($transformedAttachment[$key]) ? $this->formatDateTimeAsString(
                        $transformedAttachment[$key]
                    ) : null;
                    break;
            }
        }
    }


    /**
     * @param $transformedAttachment
     */
    private function generateTempUrl(&$transformedAttachment)
    {
        $tempPublicUrlRoot = $this->attachmentsKernel->generateTempPublicUrlRoot($transformedAttachment['savePath']);

        $urlRoot = $this->attachmentsKernel->getConfigProvider()->get('app_url') . $tempPublicUrlRoot;
        $transformedAttachment['tempUrl'] = rawurlencode("$urlRoot/{$transformedAttachment['fileName']}");
    }


    /**
     * @param $transformedAttachment
     *
     * @throws Cdn\UnsupportedCdn
     */
    private function generateSignedCdnUrls(&$transformedAttachment)
    {
        $cdnName = $this->attachmentsKernel->getCdnName($this->attachmentType);
        $signedUrlGenerator = $this->signedUrlGeneratorFactory->create($cdnName);

        $cdnUrlRoot = $this->attachmentsKernel->getCdnUrlRoot($cdnName);

        $urlRoot = "$cdnUrlRoot/{$transformedAttachment['savePath']}";

        $originalFileName = urlencode($transformedAttachment['fileName']);

        $originalUrl = "$urlRoot/$originalFileName";

        $signedOriginalUrl = $signedUrlGenerator->generate($originalUrl);

        $transformedAttachment['originalUrl'] = $signedOriginalUrl;

        // generate signed URLs for attachment variation files
        if (is_array($transformedAttachment['variationFileNames'])) {
            foreach ($transformedAttachment['variationFileNames'] as $variationName => $variationFileName) {
                $variationFileName = urlencode($variationFileName);

                $variationUrl = "$urlRoot/$variationFileName";

                $transformedAttachment['variationUrls'][$variationName] = $signedUrlGenerator->generate($variationUrl);
            }
        }
    }
}
