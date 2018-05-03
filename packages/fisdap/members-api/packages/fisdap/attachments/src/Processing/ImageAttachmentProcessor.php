<?php namespace Fisdap\Attachments\Processing;

use Fisdap\Attachments\Configuration\AttachmentConfig;
use Fisdap\Attachments\Configuration\AttachmentVariationConfig;
use Fisdap\Attachments\Configuration\ImageAttachmentVariationConfig;
use Fisdap\Attachments\Core\Kernel\AttachmentsKernel;
use Fisdap\Attachments\Core\LogsAttachmentEvents;
use Fisdap\Attachments\Entity\Attachment;
use Intervention\Image\Filters\FilterInterface;
use Intervention\Image\ImageManager;

/**
 * Leverages Intervention Image Library to process images according to attachment variation configuration
 *
 * @package Fisdap\Attachments\Processing
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ImageAttachmentProcessor extends AttachmentProcessor
{
    private $imageManager;


    /**
     * @param ImageManager               $imageManager
     * @param AttachmentsKernel $attachmentsKernel
     * @param LogsAttachmentEvents       $logger
     */
    public function __construct(
        ImageManager $imageManager,
        AttachmentsKernel $attachmentsKernel,
        LogsAttachmentEvents $logger
    ) {
        parent::__construct($attachmentsKernel, $logger);
        $this->imageManager = $imageManager;
    }


    /**
     * @inheritdoc
     */
    public function process(Attachment $attachment, AttachmentConfig $attachmentConfig)
    {
        /** @var AttachmentVariationConfig[] $variationConfigurations */
        $variationConfigurations = $attachmentConfig->variationConfigurations;

        if ($variationConfigurations === null) {
            return;
        }

        $tempSavePath = $this->attachmentsKernel->generateTempSavePath($attachment->getSavePath());

        foreach ($variationConfigurations as $variationConfig) {
            // skip if variation isn't an ImageAttachmentVariationConfig
            if (! $variationConfig instanceof ImageAttachmentVariationConfig) {
                continue;
            }

            // create fresh original image from file system
            $originalImage = $this->imageManager->make(
                $tempSavePath . DIRECTORY_SEPARATOR . $attachment->getFileName()
            );

            $filterReflection = new \ReflectionClass($variationConfig->imageProcessorFilterClassName);

            /** @var FilterInterface $filter */
            $filter = $filterReflection->newInstanceArgs($variationConfig->imageProcessorFilterConstructorArguments);


            // process original, preserving orientation, and save file
            /** @var \Intervention\Image\Image $variationImage */
            $variationImage = $originalImage->orientate()->filter($filter);

            $variationFileName = $this->generateVariationFileName($attachment, $variationConfig);

            $variationImage->save($tempSavePath . DIRECTORY_SEPARATOR . $variationFileName);


            // update entity
            $variationName = $variationConfig->name;
            $attachment->addVariationFileName($variationName, $variationFileName);
            $this->processedVariations[] = $variationName;
        }

        $this->logCompletion($attachment);
    }
}
