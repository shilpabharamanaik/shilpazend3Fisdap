<?php namespace Fisdap\Api\Client\Reports\Gateway;

/**
 * Contract for reports gateways
 *
 * @package Fisdap\Api\Client\Reports\Gateway
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
interface ReportsGateway
{
    /**
     * @param int               $programId
     * @param int               $goalSetId
     * @param Date|null         $startDate
     * @param Date|null         $endDate
     * @param int[]|null        $siteIds
     * @param int[]|null        $subjectIds
     * @param int[]             $studentIds
     * @param bool              $audited
     *
     * @return array
     */
    public function get3c2ReportData(
        $programId,
        $goalSetId,
        $startDate,
        $endDate,
        $siteIds,
        $subjectIds,
        $studentIds,
        $audited
    );
}
