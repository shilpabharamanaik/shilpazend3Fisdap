<?php namespace Fisdap\Api\Client\Students\Gateway;

use Fisdap\Api\Client\Gateway\CommonHttpGateway;
use Fisdap\Api\Client\Gateway\GetOneById;
use Fisdap\Api\Client\Gateway\RetrievesById;

/**
 * Http implementation of a ShiftsGateway
 *
 * @package Fisdap\Api\Client\Shifts\Gateway
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class HttpStudentsGateway extends CommonHttpGateway implements StudentsGateway, RetrievesById
{
    use GetOneById;


    protected static $uriRoot = '/students';


    /**
     * @inheritdoc
     */
    public function getShifts(
        $studentId,
        array $includes = null,
        array $includeIds = null,
        array $states = null,
        array $types = null,
        array $startingBetween = null,
        $firstResult = null,
        $maxResults = null
    ) {
        $query = [];

        if (is_array($includes)) {
            $query['includes'] = implode(',', $includes);
        }

        if (is_array($includeIds)) {
            $query['includeIds'] = implode(',', $includeIds);
        }

        if (is_array($states)) {
            $query['states'] = implode(',', $states);
        }

        if (is_array($types)) {
            $query['types'] = implode(',', $types);
        }

        if (is_array($startingBetween)) {
            $query['startingBetween'] = implode(',', $startingBetween);
        }

        if (is_int($firstResult)) {
            $query['firstResult'] = $firstResult;
        }

        if (is_int($maxResults)) {
            $query['maxResults'] = $maxResults;
        }

        $shifts = $this->client->get(static::$uriRoot . "/$studentId/shifts", [
            'query' => $query,
            'responseType' => $this->responseType
        ]);

        // key shifts by id
        foreach ($shifts as $key => $shift) {
            $shifts[$this->responseType == self::RESPONSE_TYPE_ARRAY ? $shift['id'] : $shift->id] = $shift;
            unset($shifts[$key]);
        }

        return $shifts;
    }
}
