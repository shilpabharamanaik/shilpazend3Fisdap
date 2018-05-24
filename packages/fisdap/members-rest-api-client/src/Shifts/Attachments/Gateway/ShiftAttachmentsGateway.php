<?php namespace Fisdap\Api\Client\Shifts\Attachments\Gateway;

use Fisdap\Api\Client\Attachments\Gateway\AttachmentsGateway;


/**
 * Contract for shift attachments gateways
 *
 * @package Fisdap\Api\Client\Shifts\Attachments\Gateway
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface ShiftAttachmentsGateway extends AttachmentsGateway
{
    /**
     * @param int $userRoleId
     *
     * @return null|int
     */
    public function getRemainingAllottedCount($userRoleId);
}