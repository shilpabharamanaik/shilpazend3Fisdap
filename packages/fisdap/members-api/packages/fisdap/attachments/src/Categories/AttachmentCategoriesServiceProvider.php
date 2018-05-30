<?php namespace Fisdap\Attachments\Categories;

use Doctrine\ORM\EntityManager;
use Fisdap\Attachments\Categories\Console\AddAttachmentCategories;
use Fisdap\Attachments\Categories\Console\ListAttachmentCategories;
use Fisdap\Attachments\Categories\Console\RemoveAttachmentCategories;
use Fisdap\Attachments\Categories\Console\RenameAttachmentCategory;
use Fisdap\Attachments\Categories\Entity\AttachmentCategory;
use Fisdap\Attachments\Categories\Queries\AttachmentCategoriesFinder;
use Fisdap\Attachments\Categories\Queries\FindsAttachmentCategories;
use Fisdap\Attachments\Categories\Repository\AttachmentCategoriesRepository;
use Fisdap\Attachments\Core\TypeMapper\MapsAttachmentTypes;
use Fisdap\Data\Repository\DoctrineRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Registers dependencies and console commands for attachment categories
 *
 * @package Fisdap\Attachments\Categories
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class AttachmentCategoriesServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function boot()
    {
        // Artisan commands
        $this->commands([
            'command.attachment.categories.add',
            'command.attachment.categories.rename',
            'command.attachment.categories.remove',
            'command.attachment.categories.list'
        ]);
    }


    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->registerCommands();
        
        $this->registerDataAccess();
    }


    private function registerCommands()
    {
        $this->app->bind('command.attachment.categories.add', AddAttachmentCategories::class);
        $this->app->bind('command.attachment.categories.rename', RenameAttachmentCategory::class);
        $this->app->bind('command.attachment.categories.remove', RemoveAttachmentCategories::class);
        $this->app->bind('command.attachment.categories.list', ListAttachmentCategories::class);
    }


    private function registerDataAccess()
    {
        $this->app->singleton(
            AttachmentCategoriesRepository::class,
            function () {
                /** @var DoctrineRepository $repo */
                $repo = $this->app->make(EntityManager::class)->getRepository(AttachmentCategory::class);
                $repo->setLogger($this->app->make('log')->getMonolog());

                return $repo;
            }
        );

        $this->app->singleton(
            FindsAttachmentCategories::class,
            function () {
                return new AttachmentCategoriesFinder(
                    $this->app->make(MapsAttachmentTypes::class),
                    $this->app->make(AttachmentCategoriesRepository::class)
                );
            }
        );
    }
}
