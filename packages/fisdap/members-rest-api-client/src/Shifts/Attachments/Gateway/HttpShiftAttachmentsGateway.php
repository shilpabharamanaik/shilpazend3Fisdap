<?php namespace Fisdap\Api\Client\Shifts\Attachments\Gateway;

use Fisdap\Api\Client\Attachments\Gateway\HttpAttachmentsGatewayTemplate;

/**
 * HTTP implementation of a ShiftAttachmentsGateway
 *
 * @package Fisdap\Api\Client\Shifts\Attachments\Gateway
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class HttpShiftAttachmentsGateway extends HttpAttachmentsGatewayTemplate implements ShiftAttachmentsGateway
{
    protected static $uriRoot = '/shifts';


    /**
     * @inheritdoc
     */
    public function getRemainingAllottedCount($userRoleId)
    {
        $response = $this->client->get("/users/contexts/$userRoleId/shifts/attachments/remaining");

        if ($response === null) {
            return $response;
        }

        return $response->remainingAllottedCount;
    }
}
