<?php namespace Fisdap\Attachments\Core;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\DBAL\Types\Type;
use Fisdap\Attachments\Core\ConfigProvider\ProvidesConfig;
use Fisdap\Attachments\Core\Kernel\DefaultAttachmentsKernel;
use Fisdap\Attachments\Core\Kernel\AttachmentsKernel;
use Fisdap\Attachments\Core\TypeMapper\AttachmentTypeMapper;
use Fisdap\Attachments\Core\TypeMapper\MapsAttachmentTypes;
use Fisdap\Attachments\Http\MimeTypeBlacklistValidator;
use Fisdap\Doctrine\Extensions\ColumnType\UuidType;
use Illuminate\Contracts\Filesystem\Factory as Filesystem;
use Illuminate\Support\ServiceProvider;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Illuminate\Validation\Factory as Validator;

/**
 * Configures and registers AttachmentsKernel, Logger, annotations, and respective dependencies
 *
 * @package Fisdap\Attachments\Core
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class AttachmentsCoreServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function boot()
    {
        // bootstrap custom Doctrine UUID generator
        if (! Type::hasType('uuid')) {
            Type::addType('uuid', UuidType::class);
        } else {
            Type::overrideType('uuid', UuidType::class);
        }

        // configure logger
        /** @var AttachmentsKernel $attachmentsKernel */
        $attachmentsKernel = $this->app->make(AttachmentsKernel::class);

        /** @var AttachmentsLogger $attachmentsLogger */
        $attachmentsLogger = $this->app->make(LogsAttachmentEvents::class);

        /** @var Logger $monologLogger */
        $monologLogger = $attachmentsLogger->getLogger();

        $streamHandler = new StreamHandler(
            $attachmentsKernel->getConfigProvider()->get('log_file'),
            $attachmentsKernel->getConfigProvider()->get('log_level')
        );

        $monologLogger->pushHandler($streamHandler);


        // add annotations
        AnnotationRegistry::registerFile(__DIR__ . '/../Configuration/Annotations.php');

        $cache = new FilesystemCache($attachmentsKernel->getConfigProvider()->get('annotation_cache_path'));

        $reader = new CachedReader(
            new AnnotationReader(),
            $cache,
            $attachmentsKernel->getConfigProvider()->get('debug')
        );
        $attachmentsKernel->setAnnotationReader($reader);


        // add MIME type blacklist validator
        /** @var Validator $validator */
        $validator = $this->app->make(Validator::class);
        $validator->extend(
            'mimetype_not_blacklisted',
            MimeTypeBlacklistValidator::class . '@validate',
            'The uploaded file type is not allowed (MIME type is on blacklist).'
        );
    }


    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->publishes([
            __DIR__ . '/../config/attachments.php' => config_path('attachments.php')
        ]);

        // register kernel
        $this->app->singleton(
            AttachmentsKernel::class,
            function () {
                return new DefaultAttachmentsKernel(
                    $this->app,
                    $this->app->make(ProvidesConfig::class),
                    $this->app->make(Filesystem::class),
                    $this->app->make(MapsAttachmentTypes::class)
                );
            }
        );


        // register attachment type mapper
        $this->app->singleton(MapsAttachmentTypes::class, function () {
            return new AttachmentTypeMapper($this->app->make(ProvidesConfig::class));
        });


        // register dedicated logger
        $this->app->singleton(LogsAttachmentEvents::class, function () {
            $env = $this->app->environment();

            return new AttachmentsLogger(new Logger("$env-attachments"));
        });


        // register mime type guesser
        $guesser = MimeTypeGuesser::getInstance();
        $guesser->register(new HoaMimeTypeGuesser());
    }
}
