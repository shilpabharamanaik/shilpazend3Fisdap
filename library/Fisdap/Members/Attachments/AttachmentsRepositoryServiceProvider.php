<?php namespace Fisdap\Members\Attachments;

use Fisdap\Data\Repository\DoctrineRepository;
use Illuminate\Support\ServiceProvider;
use Zend_Registry;
use Fisdap\Attachments\Repository\AttachmentsRepository;
use Fisdap\Attachments\Entity\Attachment;


/**
 * Provides attachment repositories
 *
 * @package Fisdap\Attachments
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class AttachmentsRepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        // register repository
        $this->app->singleton(
            AttachmentsRepository::class,
            function () {
                /** @var DoctrineRepository $repo */
                $repo = \Zend_Registry::get('doctrine')->getEntityManager()->getRepository(Attachment::class);
                $repo->setLogger(\Zend_Registry::get('logger'));
                return $repo;
            }
        );
    }
} 