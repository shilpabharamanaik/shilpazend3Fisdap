<?php
/**
 * Class Fisdap_Reports_Narrative
 * This is the Narrative Report class
 * Refer to Fisdap_Reports_Report for more documentation
 */
class Fisdap_Reports_Productivity extends Fisdap_Reports_Report
{
    public $header = '';

    public $footer = '';

    public $formComponents = array(
        'shiftInformationForm' => array(
            'title' => 'Select shift information',
			'options' => array(
				'pickPatientType' => FALSE,
			),
        ),
		'Reports_Form_CertificationLevelsForm' => array(
			'title' => 'Select certification(s)',
		),
    );

   /**
     * Run a query and any processing logic that produces the data contained in the report
     * Return a multidimensional array and it will be rendered as tables
     * OR return a string and it will be rendered as HTML
     * @return array
     */
    public function runReport() {
		$program = \Fisdap\Entity\ProgramLegacy::getCurrentProgram()->id;
		
		// clean up the site info
		$site_ids = $this->getSiteIds();
		
		$start_date = $this->config['startDate'];
		$end_date = $this->config['endDate'];
		
        // Run a query to get data.
        $siteRepo = \Fisdap\EntityUtils::getRepository('SiteLegacy');
        $baseRepo = \Fisdap\EntityUtils::getRepository('BaseLegacy');
        $preceptorRepo = \Fisdap\EntityUtils::getRepository('PreceptorLegacy');
		$data = $siteRepo->getProductivityInfo($site_ids, $program, $this->config);
		$siteNames = $siteRepo->getFormOptionsByProgramWithSiteType($program, array("field", "clinical", "lab"), null, null, null, true);
		
        // make a table
		foreach($data as $site_id => $baseInfo) {

                        $siteType = $siteNames[$site_id]['type'];
                        switch($siteType){
                            case 'field':
                                $word = 'Bases';
                                break;
                            case 'clinical':
                                $word = 'Departments';
                                break;
                            case 'lab':
                                $word = 'Bases';
                                break;
                            default:
                                $word = 'Bases';                         
                        }
                        
			$siteName = $siteNames[$site_id]['name'];
			
			$siteTable = array(
				'title' => $siteName . " - " . $word,
				'nullMsg' => "No bases found.",
				'head' => array(
					'0' => array( // there's only 1 row header for this report
						'Name',
						'Hours Available',
						'Hours Chosen',
						'Shifts Chosen',
						'Shifts Locked',
						'Hours Locked',
						'Patients',
						'Team Lead',
						'% Student Led',
						'ALS Airways',
						'IVs',
						'Meds',
						'Unique Students',
					),
				),
				'body' => array(),
			);
			
			//Deal with bases
			$bases = $baseRepo->getBaseAssociationsByProgramOptimized($site_id, $program);
			
			foreach($bases as $thisBase) {
				$base = $baseInfo['bases'][$thisBase['base']['id']];
				
				if ($base['patients_count'] > 0) {
					$studentLed = round($base['team_leads_count']/$base['patients_count']*100, 2);
				} else {
					$studentLed = 'n/a';
				}
				
				$siteTable['body'][] = array(
					array("data" => $thisBase['base']['name'], "class" => "noSum noAverage noMin noMax"),
					array("data" => isset($base['hours_available']) ? $base['hours_available'] : "0.00", "class" => "right"),
					array("data" => isset($base['hours_chosen']) ? $base['hours_chosen'] : "0.00", "class" => "right"),
					array("data" => isset($base['shifts_chosen']) ? $base['shifts_chosen'] : "0", "class" => "right"),
					array("data" => isset($base['shifts_locked']) ? $base['shifts_locked'] : "0", "class" => "right"),
					array("data" => isset($base['hours_locked']) ? $base['hours_locked'] : "0.00", "class" => "right"),
					array("data" => isset($base['patients_count']) ? $base['patients_count'] : "0", "class" => "right"),
					array("data" => isset($base['team_leads_count']) ? $base['team_leads_count'] : "0", "class" => "right"),
					array("data" => $studentLed, "class" => "right"),
					array("data" => isset($base['airway_count']) ? $base['airway_count'] : "0", "class" => "right"),
					array("data" => isset($base['iv_count']) ? $base['iv_count'] : "0", "class" => "right"),
					array("data" => isset($base['meds_count']) ? $base['meds_count'] : "0", "class" => "right"),
					array("data" => isset($base['unique_students']) ? $base['unique_students'] : "0", "class" => "right"),
			    );
			}
			
			// add the footer to calculate totals, but only if there's more than one row
			if (count($siteTable['body']) > 1) {
				$average = array(array("data" => "Average:", "class" => "right"));
				$sum = array(array("data" => "Total:", "class" => "right"));
				$min = array(array("data" => "Min:", "class" => "right"));
				$max = array(array("data" => "Max:", "class" => "right"));
				
				$numColumns = count(current($siteTable['body']));
				for ($i = 1; $i < $numColumns; $i++) {
					$average[] = array("data" => "-", "class" => "right");
					$sum[] = array("data" => "-", "class" => "right");
					$min[] = array("data" => "-", "class" => "right");
					$max[] = array("data" => "-", "class" => "right");
				}
	
				$siteTable['foot']["average"] = $average;		
				$siteTable['foot']["sum"] = $sum;
				$siteTable['foot']["min"] = $min;
				$siteTable['foot']["max"] = $max;
			}
			
			$this->data[] = array("type" => "table", "content" => $siteTable);
			
			//Now deal with preceptors
			$preceptorTable = array(
				'title' => $siteName . " - Preceptors",
				'nullMsg' => "No preceptors found.",
				'head' => array(
					'0' => array( // there's only 1 row header for this report
						'Name',
						'Start Date',
						'Hours Available',
						'Hours Chosen',
						'Shifts Chosen',
						'Shifts Locked',
						'Hours Locked',
						'Patients',
						'Team Lead',
						'% Student Led',
						'ALS Airways',
						'IVs',
						'Meds',
						'Unique Students',
					),
				),
				'body' => array(),
			);
			
			$preceptors = $preceptorRepo->getPreceptorsOptimized($program, true, $site_id, $active_matters = true);
			
			foreach($preceptors as $thisPreceptor) {
				$preceptor = $baseInfo['preceptors'][$thisPreceptor['id']];
				
				if ($preceptor['patients_count'] > 0) {
					$studentLed = round($preceptor['team_leads_count']/$preceptor['patients_count']*100, 2);
				} else {
					$studentLed = 'n/a';
				}
				
				$preceptorTable['body'][] = array(
					array("data" => $thisPreceptor['first_name'] . " " . $thisPreceptor['last_name'], "class" => "noSum noAverage noMin noMax"),
					array("data" => isset($preceptor['first_shift_date']) ? $preceptor['first_shift_date'] : "", "class" => "noSum noAverage noMin noMax"),
					array("data" => isset($preceptor['hours_available']) ? $preceptor['hours_available'] : "0.00", "class" => "right"),
					array("data" => isset($preceptor['hours_chosen']) ? $preceptor['hours_chosen'] : "0.00", "class" => "right"),
					array("data" => isset($preceptor['shifts_chosen']) ? $preceptor['shifts_chosen'] : "0", "class" => "right"),
					array("data" => isset($preceptor['shifts_locked']) ? $preceptor['shifts_locked'] : "0", "class" => "right"),
					array("data" => isset($preceptor['hours_locked']) ? $preceptor['hours_locked'] : "0.00", "class" => "right"),
					array("data" => isset($preceptor['patients_count']) ? $preceptor['patients_count'] : "0", "class" => "right"),
					array("data" => isset($preceptor['team_leads_count']) ? $preceptor['team_leads_count'] : "0", "class" => "right"),
					array("data" => $studentLed, "class" => "right"),
					array("data" => isset($preceptor['airway_count']) ? $preceptor['airway_count'] : "0", "class" => "right"),
					array("data" => isset($preceptor['iv_count']) ? $preceptor['iv_count'] : "0", "class" => "right"),
					array("data" => isset($preceptor['meds_count']) ? $preceptor['meds_count'] : "0", "class" => "right"),
					array("data" => isset($preceptor['unique_students']) ? $preceptor['unique_students'] : "0", "class" => "right"),
			    );
			}
			
			// add the footer to calculate totals, but only if there's more than one row
			if (count($preceptorTable['body']) > 1) {
				// add the first two rows, since those don't contain numerical data
				$average = array("", array("data" => "Average:", "class" => "right"));
				$sum = array("", array("data" => "Total:", "class" => "right"));
				$min = array("", array("data" => "Min:", "class" => "right"));
				$max = array("", array("data" => "Max:", "class" => "right"));
				
				$numColumns = count(current($preceptorTable['body']));
				for ($i = 1; $i < $numColumns-1; $i++) {
					$average[] = array("data" => "-", "class" => "right");
					$sum[] = array("data" => "-", "class" => "right");
					$min[] = array("data" => "-", "class" => "right");
					$max[] = array("data" => "-", "class" => "right");
				}
	
				$preceptorTable['foot']["average"] = $average;		
				$preceptorTable['foot']["sum"] = $sum;
				$preceptorTable['foot']["min"] = $min;
				$preceptorTable['foot']["max"] = $max;
			}
			
			$this->data[] = array("type" => "table", "content" => $preceptorTable);
		}
    }

    /**
    * Return a short label/description of the report using report configuration
    * Useful in listing saved Report Configurations as a saved report history
    * Override this if your report should display something different!
    */
    public function getShortConfigLabel() {

        // get the number of sites, or the site name if only one
        $siteLabel = '';

        $siteIds = $this->getSiteIds();

        if(isset($siteIds)) {
            $numSiteIds = count($siteIds);

            if($numSiteIds === 1) {
                $site = \Fisdap\EntityUtils::getEntity('SiteLegacy', $siteIds[0]);
                return $site->name;
            } else {
                return $numSiteIds . ' sites';
            }
        } else {
            return 'No sites';
        }
   }

}