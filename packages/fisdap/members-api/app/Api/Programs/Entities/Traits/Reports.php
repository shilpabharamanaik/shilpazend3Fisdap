<?php namespace Fisdap\Api\Programs\Entities\Traits;

use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToMany;
use Fisdap\Entity\ProgramReport;
use Fisdap\EntityUtils;


/**
 * Class Reports
 *
 * @package Fisdap\Api\Programs\Entities\Traits
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait Reports
{
    /**
     * @OneToMany(targetEntity="ProgramReport", mappedBy="program")
     * @JoinColumn(name="Program_id", referencedColumnName="program_id")
     */
    protected $reports;
    
    
    public function getActiveReports()
    {
        $active_reports = array();

        // get all the reports available to this profession
        $reports = EntityUtils::getRepository('Report')->getAvailableReportsByProfession($this->profession->id);

        // loop through all available reports add the active ones
        foreach ($reports as $report) {
            // default to active in case there is no link
            $active = true;

            // loop through all links to see if this report is active for this program
            foreach ($this->reports as $reportLink) {
                if ($report->id == $reportLink->report->id && !$reportLink->active) {
                    $active = false;
                }
            }

            // if this report is active and not a standalone report, add it to the list
            if ($active && !$report->standalone) {
                $active_reports[] = $report;
            }
        }


        @usort($active_reports, array('self', 'sortReportsByName'));
        return $active_reports;
    }


    public static function sortReportsByName($a, $b)
    {
        return ($a->name < $b->name ? -1 : 1);
    }


    public function isActiveReport($report_id)
    {
        foreach ($this->reports as $reportLink) {
            if ($reportLink->report->id == $report_id) {
                return $reportLink->active;
            }
        }

        // if there's no link, default it to active
        return true;
    }


    public function isAssociatedReport($report_id)
    {
        foreach ($this->reports as $reportLink) {
            if ($reportLink->report->id == $report_id) {
                return true;
            }
        }
        return false;
    }


    public function addReport($report_id, $active = 1)
    {
        // if this report is already associated with this program, just toggle it
        if ($this->isAssociatedReport($report_id)) {
            $this->toggleReport($report_id, $active);
            return;
        }

        // otherwise, add it to the list of associations
        $report = EntityUtils::getEntity("Report", $report_id);
        $association = new ProgramReport();
        $association->program = $this;
        $association->report = $report;
        $association->active = $active;
        $association->save();
        $this->reports->add($association);
    }


    public function toggleReport($report_id, $active)
    {
        foreach ($this->reports as $reportLink) {
            if ($reportLink->report->id == $report_id) {
                $reportLink->active = $active;
                $reportLink->save();

                return;
            }
        }

        // if we're still here, there wasn't a report link for this report. Make one.
        $this->addReport($report_id, $active);
        return;
    }
}
