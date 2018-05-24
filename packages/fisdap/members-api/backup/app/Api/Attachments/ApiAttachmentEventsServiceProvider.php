<?php namespace Fisdap\Api\Attachments;

use Fisdap\Api\Attachments\Listeners\StudentUserContextIdMatchesAttachmentUserContextId;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;


/**
 * Class ApiAttachmentEventsServiceProvider
 *
 * @package Fisdap\Api\Attachments
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
final class ApiAttachmentEventsServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    protected $subscribe = [
        StudentUserContextIdMatchesAttachmentUserContextId::class
    ];
}