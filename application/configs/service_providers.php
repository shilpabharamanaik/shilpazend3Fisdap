<?php

use Fisdap\Members\Commerce\GreatPlainsServiceProvider;
use AscendLearning\Lti\LtiServiceProvider;
use Fisdap\Api\Client\ApiClientServiceProvider;
use Fisdap\Auth\JblAuthServiceProvider;
use Fisdap\Api\Users\CurrentUser\CurrentUserServiceProvider;
use Fisdap\Data\Repository\EntityRepositoryServiceProvider;
use Fisdap\Doctrine\Extensions\FisdapDoctrineExtensionsServiceProvider;
use Fisdap\Members\Attachments\AttachmentsRepositoryServiceProvider;
use Fisdap\Members\Commerce\BraintreeServiceProvider;
use Fisdap\Members\Commerce\OrdersServiceProvider;
use Fisdap\Members\Config\ConfigServiceProvider;
use Fisdap\Members\Logging\MailLogServiceProvider;
use Fisdap\Members\Lti\Session\LtiSessionServiceProvider;
use Fisdap\Members\Queue\QueueServiceProvider;
use Fisdap\Members\ServiceProviders\JobsServiceProvider;
use Fisdap\Members\ServiceProviders\MembersHealthChecksServiceProvider;
use Fisdap\Members\ServiceProviders\ProductsServiceProvider;
use Fisdap\Members\ServiceProviders\RedisServiceProvider;
use Fisdap\Members\ServiceProviders\UsersServiceProvider;
use Fisdap\Members\ServiceProviders\ValidationServiceProvider;
use Fisdap\Service\DataExport\PdfGeneratorProvider;
use Illuminate\Bus\BusServiceProvider;
use Illuminate\Encryption\EncryptionServiceProvider;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Mail\MailServiceProvider;
use Illuminate\Pipeline\PipelineServiceProvider;
use Illuminate\Translation\TranslationServiceProvider;
use Illuminate\View\ViewServiceProvider;


/**
 * Return an array of class names, each of which extend the abstract ServiceProvider class
 */
return [
    ConfigServiceProvider::class,
    EncryptionServiceProvider::class,
    RedisServiceProvider::class,
    QueueServiceProvider::class,
    BusServiceProvider::class,
    PipelineServiceProvider::class,
    JobsServiceProvider::class,
    FilesystemServiceProvider::class,
    MailServiceProvider::class,
    MailLogServiceProvider::class,
    TranslationServiceProvider::class,
    ValidationServiceProvider::class,
    ViewServiceProvider::class,
    BraintreeServiceProvider::class,
    MembersHealthChecksServiceProvider::class,
    EntityRepositoryServiceProvider::class,
    CurrentUserServiceProvider::class,
    PdfGeneratorProvider::class,
    ApiClientServiceProvider::class,
    FisdapDoctrineExtensionsServiceProvider::class,
    AttachmentsRepositoryServiceProvider::class,
    OrdersServiceProvider::class,
    GreatPlainsServiceProvider::class,
    LtiSessionServiceProvider::class,
    LtiServiceProvider::class,
    UsersServiceProvider::class,
    ProductsServiceProvider::class,
    JblAuthServiceProvider::class,
];
