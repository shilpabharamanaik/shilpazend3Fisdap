<?php namespace Fisdap\Api\Client\Reports\Gateway;

use Fisdap\Api\Client\Gateway\CommonHttpGateway;

/**
 * Http implementation of a ReportsGateway
 *
 * @package Fisdap\Api\Client\Reports\Gateway
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
class HttpReportsGateway extends CommonHttpGateway implements ReportsGateway
{
    protected static $uriRoot = "/programs";


    /**
     * @inheritdoc
     */
    public function get3c2ReportData($programId, $goalSetId, $startDate, $endDate, $siteIds, $subjectIds, $studentIds, $audited)
    {
        $query = [];

        if (is_int($goalSetId)) {
            $query['goalSetId'] = $goalSetId;
        } else {
            $goalSetId = intval($goalSetId);
            if (is_int($goalSetId)) {
                $query['goalSetId'] = $goalSetId;
            }
        }

        if ($startDate instanceof \DateTime) {
            $query['startDate'] = date_format($startDate, 'Y-m-d 00:00:00');
        } elseif (is_string($startDate)) {
            $query['startDate'] = $startDate;
        }

        if ($endDate instanceof \DateTime) {
            $query['endDate'] = date_format($endDate, 'Y-m-d 00:00:00');
        } elseif (is_string($endDate)) {
            $query['endDate'] = $endDate;
        }

        if (is_array($siteIds)) {
            $query['siteIds'] = implode(',', $siteIds);
        }

        if (is_array($subjectIds)) {
            $query['subjectIds'] = implode(',', $subjectIds);
        }

        if (is_array($studentIds)) {
            $query['studentIds'] = implode(',', $studentIds);
        }

        if (is_bool($audited)) {
            $query['audited'] = ($audited ? 1 : 0);
        }

        $response = $this->client->request("GET", "/programs/$programId/reports/3c2", [
            'query' => $query,
            'responseType' => self::RESPONSE_TYPE_ARRAY
        ]);

        return $response;
    }
}
