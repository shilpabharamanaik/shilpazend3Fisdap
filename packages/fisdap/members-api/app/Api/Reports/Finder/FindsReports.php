<?php namespace Fisdap\Api\Reports\Finder;

use Fisdap\Api\ResourceFinder\FindsResources;

/**
 * Contract for retrieving Reports
 *
 * @package Fisdap\Api\Reports
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
interface FindsReports extends FindsResources
{
    /**
     * @param $goalSetId
     * @param $startDate
     * @param $endDate
     * @param $subjectTypeIds
     * @param $siteIds
     * @param $studentIds
     * @param $audited
     */
    public function get3c2ReportData($goalSetId, $startDate, $endDate, $subjectTypeIds, $siteIds, $studentIds, $audited);
}
