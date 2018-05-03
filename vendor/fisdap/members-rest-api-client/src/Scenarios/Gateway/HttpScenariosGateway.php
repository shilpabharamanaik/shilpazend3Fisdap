<?php namespace Fisdap\Api\Client\Scenarios\Gateway;

use Fisdap\Api\Client\Gateway\CommonHttpGateway;


/**
 * Http implementation of a ScenariosGateway
 *
 * @package Fisdap\Api\Client\Scenarios\Gateway
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
class HttpScenariosGateway extends CommonHttpGateway implements ScenariosGateway
{
    protected static $uriRoot = "/scenarios";


    /**
     * @inheritdoc
     */
    public function exportToALSI($scenarioId)
    {
        $response = $this->client->request("GET", "/scenarios/$scenarioId/alsi", [
            'responseType' => self::RESPONSE_TYPE_ARRAY
        ]);

        return $response;
    }
}
