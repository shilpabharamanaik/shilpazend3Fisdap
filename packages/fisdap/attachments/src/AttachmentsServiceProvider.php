<?php namespace Fisdap\Attachments;

use Doctrine\ORM\EntityManager;
use Fisdap\Attachments\Associations\AssociatedEntityFinder;
use Fisdap\Attachments\Associations\FindsAssociatedEntities;
use Fisdap\Attachments\Cdn\SignedUrlGeneratorFactory;
use Fisdap\Attachments\Core\Kernel\AttachmentsKernel;
use Fisdap\Attachments\Core\TypeMapper\MapsAttachmentTypes;
use Fisdap\Attachments\Entity\Attachment;
use Fisdap\Attachments\Queries\AttachmentsFinder;
use Fisdap\Attachments\Queries\FindsAttachments;
use Fisdap\Attachments\Repository\AttachmentsRepository;
use Fisdap\Attachments\Transformation\AttachmentTransformer;
use Fisdap\Attachments\Transformation\TransformsAttachments;
use Fisdap\Data\Repository\DoctrineRepository;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;

/**
 * Configures and registers Attachments Manager, Logger, annotations, and respective dependencies
 *
 * @package Fisdap\Attachments
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class AttachmentsServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        // register repository
        $this->app->singleton(
            AttachmentsRepository::class,
            function () {
                /** @var DoctrineRepository $repo */
                $repo = $this->app->make(EntityManager::class)->getRepository(Attachment::class);
                $repo->setLogger($this->app->make('log')->getMonolog());

                return $repo;
            }
        );


        // register associated entity finder
        $this->app->singleton(FindsAssociatedEntities::class, function () {
            return new AssociatedEntityFinder(
                $this->app,
                $this->app->make(AttachmentsKernel::class)
            );
        });


        // register attachments finder
        $this->app->singleton(FindsAttachments::class, function () {
            return new AttachmentsFinder(
                $this->app->make(MapsAttachmentTypes::class),
                $this->app->make(AttachmentsRepository::class),
                $this->app->make(EventDispatcher::class)
            );
        });


        // register transformer
        $this->app->singleton(TransformsAttachments::class, function () {
            return new AttachmentTransformer(
                $this->app->make(AttachmentsKernel::class),
                $this->app->make(SignedUrlGeneratorFactory::class)
            );
        });
    }
}
