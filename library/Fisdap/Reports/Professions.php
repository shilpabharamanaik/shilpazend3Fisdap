<?php
/**
 * Class Fisdap_Reports_Professions
 *
 * Use this report to see the programs in a given profession
 */
class Fisdap_Reports_Professions extends Fisdap_Reports_Report
{
    public $header = '';

    public $footer = '';

    public $formComponents = array(
        'Reports_Form_ProfessionsForm' => array(
            'title' => 'Select one profession',
        ),
    );
	
	/**
     * This report is only visible to staff
     */
	public static function hasPermission($userContext) {
		return $userContext->getUser()->isStaff();
	}

    /**
     * Run a query and any processing logic that produces the data contained in the report
     * Return a multidimensional array and it will be rendered as tables
     * OR return a string and it will be rendered as HTML
     * @return array
     */
    public function runReport() {
		// get profession info
		$profession = \Fisdap\EntityUtils::getEntity('Profession', $this->config['profession']);

		// create the header row
		$header = array("ID", "Program", "Location", "Contact", "Last Order");

		// make the table
        $programTable = array(
			'title' => $profession->name . " Programs",
			'nullMsg' => "No programs found.",
            'head' => array('0' => $header),
            'body' => array(),
        );

		// get the programs for this profession
		$programRepo = \Fisdap\EntityUtils::getRepository("ProgramLegacy");
		$programs = $programRepo->getByProfession($profession->id);
		foreach ($programs as $program) {
			$mostRecentOrder = $programRepo->getMostRecentOrderDate($program->id);
			if ($mostRecentOrder) {
				$mostRecentOrderDate = new DateTime($mostRecentOrder);
				$order = "<span class='hidden'>".$mostRecentOrderDate->format('Ymd')."</span>".$mostRecentOrderDate->format("M j, Y");
			} else {
				$order = "";
			}

			$programTable['body'][] = array(
				array(
					'data' => $program->id,
					'class' => '',
				),
				array(
					'data' => $program->name,
					'class' => '',
				),
				array(
					'data' => $program->city . ", " . $program->state . " (" .$program->country . ")",
					'class' => '',
				),
				array(
					'data' => $program->getProgramContactName(),
					'class' => '',
				),
				array(
					'data' => $order,
					'class' => '',
				)
			);
		}

		$this->data['programs'] = array("type" => "table",
										 "content" => $programTable);
    }
	
	/**
	 * Return a custom short label/description of the productivity report
	 * Overrides parent method
	 */
	public function getShortConfigLabel() {
		// get profession info
		$profession = \Fisdap\EntityUtils::getEntity('Profession', $this->config['profession']);

		$label = $profession->name . " Programs";
	
	 	// return the label
		return $label;
	}

}