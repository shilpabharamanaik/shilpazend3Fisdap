<?php

use Fisdap\RewardPoint\RewardPointService;

/**
 * Class Fisdap_Reports_AnnualSales
 *
 * Use this report to see annual sales by program
 */
class Fisdap_Reports_AnnualSales extends Fisdap_Reports_Report
{
    public $header = '';

    public $footer = '';

    public $formComponents = array(
        'Reports_Form_YearRangeForm' => array(
            'title' => 'Select the year range',
        ),
    );

    protected $rewardPointService;

    /**
     * This report is only visible to staff
     */
    public static function hasPermission($userContext)
    {
        return $userContext->getUser()->isStaff();
    }

    /**
     * Run a query and any processing logic that produces the data contained in the report
     * Return a multidimensional array and it will be rendered as tables
     * OR return a string and it will be rendered as HTML
     * @return array
     */
    public function runReport()
    {

        ini_set('memory_limit', '2048M');

        $this->rewardPointService = new RewardPointService();

        // get date range info
        $start_year = $this->config['start_year'];
        $end_year = $this->config['end_year'];

        // create the header row
        $header = array("ID", "Program", "State", "Contact", "Reward Points", "Discounts", "YTD Sales (Prev Year)");
        $years = array();
        for ($y = $start_year; $y <= $end_year; $y++) {
            $years[] = $y;
            $header[] = $y;
        }

        // make the table
        $programTable = array(
            'title' => "Annual Sales",
            'nullMsg' => "No programs found.",
            'head' => array('0' => $header),
            'body' => array(),
        );

        // get the sales data for each year
        $orderRepo = \Fisdap\EntityUtils::getRepository("Order");
        $allPrograms = array();
        foreach ($years as $year) {
            $programs = $orderRepo->getAnnualSalesByProgramAndYear($year);
            foreach ($programs as $program) {
                $programEntity = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $program['id']);

                $allPrograms[$program['id']][$year] = $program['total'];

                if (!in_array($program['name'], $allPrograms[$program['id']])) {
                    
                    if ($programEntity->getProgramContactuser()) {
                        $contactEmail = $programEntity->getProgramContactUser()->getEmail();
                    } else {
                        $contactEmail = "N/A";
                    }

                    $allPrograms[$program['id']]["name"] = $program['name'];
                    $allPrograms[$program['id']]["state"] = $program['state'];
                    $allPrograms[$program['id']]["total"] = $program['total'];
                    $allPrograms[$program['id']]['contact'] = $programEntity->getProgramContactName() . " / " . $contactEmail;
                    //$allPrograms[$program['id']]['points'] = null;
                    $allPrograms[$program['id']]['points'] = $this->calculatePoints($program['id']);
                    $allPrograms[$program['id']]['discounts'] = $this->getDiscountText($program['id']);

                    $currentDate = new DateTime();
                    $lastYear = new DateTime();
                    $lastYear->modify('-1 year');

                    $ytdSales = $orderRepo->getYearToDateSalesByProgram($lastYear->format('Y'), $currentDate->format('m-d'), $program['id']);
                    $allPrograms[$program['id']]['ytd'] = $ytdSales[0]['ytd'];
                }
            }
        }

        // now build the table body
        foreach ($allPrograms as $program_id => $program) {
            $row = array(
                array(
                    'data' => $program_id,
                    'class' => 'noSum',
                ),
                array(
                    'data' => $program['name'],
                    'class' => 'noSum',
                ),
                array(
                    'data' => $program['state'],
                    'class' => 'noSum',
                ),
                array(
                    'data' => $program['contact'],
                    'class' => 'noSum',
                ),
                array(
                    'data' => $program['points'],
                    'class' => 'noSum',
                ),
                array(
                    'data' => $program['discounts'],
                    'class' => 'noSum',
                ),
                array(
                    'data' => $program['ytd'],
                    'class' => 'right currency',
                ),
            );
            foreach ($years as $year) {
                $row[] = array(
                    'data' => $program[$year],
                    'class' => 'right currency',
                );
            }
            $programTable['body'][] = $row;
        }

        // add the footer to calculate totals, but only if there's more than one row
        if (count($programTable['body']) > 1) {
            $footer = array(
                array("data" => "", "class" => "right"),
                array("data" => "", "class" => "right"),
                array("data" => "", "class" => "right"),
                array("data" => "", "class" => "right"),
                array("data" => "", "class" => "right"),
                array("data" => "Total:", "class" => "right"),
            );

            for ($i = 1; $i <= (count($years) + 1); $i++) {
                $footer[] = array("data" => "-", "class" => "right currency");
            }

            $programTable['foot']["sum"] = $footer;
        }

        $this->data['programs'] = array("type" => "table",
            "content" => $programTable);
    }

    /**
     * Return a custom short label/description of the productivity report
     * Overrides parent method
     */
    public function getShortConfigLabel()
    {
        // get profession info
        $profession = \Fisdap\EntityUtils::getEntity('Profession', $this->config['profession']);

        $label = $profession->name . " Programs";

        // return the label
        return $label;
    }

    private function calculatePoints($programid){

        $totalPoints =
            $this->rewardPointService->calculatePoints('donated', $programid) +
            $this->rewardPointService->calculatePoints('validated', $programid) +
            $this->rewardPointService->calculatePoints('individual_review', $programid) +
            $this->rewardPointService->calculatePoints('consensus_review', $programid) +
            $this->rewardPointService->calculatePoints('bonus', $programid) +
            $this->rewardPointService->calculatePoints('spent', $programid)
        ;

        return $totalPoints;
    }

    private function getDiscountText($programid){

        $discounts = $this->rewardPointService->getDiscounts($programid, true, false);

        $discountArray = array();

        foreach($discounts as $discount){
            if (!($discount['Configuration'] & 2) && $discount['PercentOff'] > 0) {
                $acct_type = $discount['Type'];
                $prod_desc = $this->getProductDescription($discount['Configuration']);

                if($acct_type != 'All'){
                    $discountArray[] = $discount['PercentOff']."% off $prod_desc for $acct_type accounts";
                }else{
                    $discountArray[] = $discount['PercentOff']."% off $prod_desc for all accounts";
                }
            }
        }
        $discountString = implode("\n", $discountArray);
        return $discountString;
    }

    private function getProductDescription($config){
        $description = array();

        if (\Fisdap\Entity\SerialNumberLegacy::configurationHasProductAccess($config, 'tracking')) {
            $description[] = 'Tracking';
        }
        if (\Fisdap\Entity\SerialNumberLegacy::configurationHasProductAccess($config, 'pda')) {
            $description[] = 'PDA';
        }
        if (\Fisdap\Entity\SerialNumberLegacy::configurationHasProductAccess($config, 'scheduler')) {
            $description[] = 'Scheduler';
        }
        if (\Fisdap\Entity\SerialNumberLegacy::configurationHasProductAccess($config, 'testing')) {
            $description[] = 'Testing';
        }
        if (\Fisdap\Entity\SerialNumberLegacy::configurationHasProductAccess($config, 'prep')) {
            $description[] = 'Study Tools (paramedic)';
        }
        if (\Fisdap\Entity\SerialNumberLegacy::configurationHasProductAccess($config, 'preceptortraining')) {
            $description[] = 'Clinical Educator Training';
        }
        if (\Fisdap\Entity\SerialNumberLegacy::configurationHasProductAccess($config, 'emtb_study_tools')) {
            $description[] = 'Study Tools (basic)';
        }
        if (\Fisdap\Entity\SerialNumberLegacy::configurationHasProductAccess($config, 'emtb_comprehensive_exams')) {
            $description[] = 'Comprehensive Exams (basic)';
        }
        if (\Fisdap\Entity\SerialNumberLegacy::configurationHasProductAccess($config, 'para_comprehensive_exams')) {
            $description[] = 'Comprehensive Exams (paramedic)';
        }
        if (\Fisdap\Entity\SerialNumberLegacy::configurationHasProductAccess($config, 'emtb_unit_exams')) {
            $description[] = 'Unit Exams (basic)';
        }
        if (\Fisdap\Entity\SerialNumberLegacy::configurationHasProductAccess($config, 'para_unit_exams')) {
            $description[] = 'Unit Exams (paramedic)';
        }

        return implode(', ', $description);
    }



}