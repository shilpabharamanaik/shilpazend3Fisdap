<?php namespace Fisdap\Attachments\Cdn;

use Aws\Sdk;
use Fisdap\Attachments\Core\Kernel\AttachmentsKernel;

/**
 * Generates AWS CloudFront signed URLs
 *
 * @package Fisdap\Attachments\Cdn
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class CloudFrontSignedUrlGenerator extends SignedUrlGenerator
{
    /**
     * @var Sdk
     */
    private $aws;


    /**
     * @param Sdk                        $aws
     * @param AttachmentsKernel $attachmentsKernel
     */
    public function __construct(Sdk $aws, AttachmentsKernel $attachmentsKernel)
    {
        $this->aws = $aws;
        parent::__construct($attachmentsKernel);
    }


    /**
     * @param string $unsignedUrl
     *
     * @return string
     */
    public function generate($unsignedUrl)
    {
        $cloudFront = $this->aws->createCloudFront();

        $expires = time() + $this->attachmentsKernel->getConfigProvider()->get('cdn.cloudfront.signed_url_ttl');
        $privateKey = $this->attachmentsKernel->getConfigProvider()->get('cdn.cloudfront.private_key_path');
        $keyPairId = $this->attachmentsKernel->getConfigProvider()->get('cdn.cloudfront.key_pair_id');

        // generate signed URL for original attachment file
        $signedOriginalUrl = $cloudFront->getSignedUrl(
            [
                'url'         => $unsignedUrl,
                'expires'     => $expires,
                'private_key' => $privateKey,
                'key_pair_id' => $keyPairId
            ]
        );

        return $signedOriginalUrl;
    }
}
