<?php namespace Fisdap\Api\Client\Scenarios\Gateway;

/**
 * Contract for scenarios gateways
 *
 * @package Fisdap\Api\Client\Scenarios\Gateway
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
interface ScenariosGateway
{
    /**
     * @param int               $scenarioId
     *
     * @return array
     */
    public function exportToALSI($scenarioId);
}
