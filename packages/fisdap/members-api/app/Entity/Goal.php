<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Fisdap\EntityUtils;


/**
 * Entity class for Goal requirements
 * 
 * @Entity(repositoryClass="Fisdap\Data\Goal\DoctrineGoalRepository")
 * @Table(name="fisdap2_goals")
 */
class Goal extends GoalBase
{
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;
	
	/**
     * @ManyToOne(targetEntity="GoalSet", inversedBy="goals")
	 * @JoinColumn(name="goal_set_id", referencedColumnName="id")
     */
	protected $goalSet;

	/**
     * @ManyToOne(targetEntity="GoalDef", inversedBy="goals")
	 * @JoinColumn(name="goal_def_id", referencedColumnName="id")
     */
	protected $def;
	
	/**
	 * @Column(type="string", length=60)
	 */
	protected $name='';
	
	public function get_name()
	{
		return ($this->name=='') ? $this->def->name : $this->name;
	}
	
	public function set_name($name)
	{
		if ($this->def->name == $name) {
			$name = '';
		}
		
		$this->name = $name;
	}
	
	//public function get_name_with_category() { return $this->def->category . ': ' . $this->name; }
	
	/**
	 * @Column(type="integer", nullable=true)
	 */
	protected $number_required=0;
	
	/**
	 * @Column(type="integer", nullable=true)
	 */
	protected $max_last_data;
	
	/**
	 * @Column(type="decimal", scale=2, precision=3, nullable=true)
	 */
	protected $percent_successful;

	/**
	 * @Column(type="boolean")
	 */
	protected $team_lead=false;
	
	/**
	 * @Column(type="boolean")
	 */
	protected $interview=false;

	/**
	 * @Column(type="boolean")
	 */
	protected $exam=false;

	/**
	 * @Column(type="string", length=255)
	 * Params for goals in goalDef
	 */
	protected $params='';
	
	public function set_goal_set_id($goalSetId)
	{
		$this->goalSet = EntityUtils::getEntity('GoalSet', $goalSetId);
	}
	
	public function set_goal_def_id($goalDefId)
	{
		$this->def = EntityUtils::getEntity('GoalDef', $goalDefId);
		//$this->goal_def_id = $goalDefId;
	}
	
	public function getGoalSummary()
	{
		// recent data only?
		$maxLastData = $this->max_last_data;
		$maxDataInfo = 'Using '
			. (($maxLastData) ? ' last '.$maxLastData : ' all data within date range');
		
		//$numRequiredDesc = ($this->percent_successful) ?
		//	$this->number_required.' and '. ($this->percent_successful*100).'%':
		//	$this->number_required. ' out of last ' .
		
		// max last data is 1 of 2:
		// - percent successful used:		percent successful on max_last_data
		// - %succ not used:				number_required out of max_last_data
		
		if ($this->percent_successful) {
			$percent = number_format($this->percent_successful * 100, 2) .'%';
			$txt = ', ' . $percent . ' successful';

			if ($maxLastData) {
				$txt .= ' in at most last ' . $maxLastData . ' occurrences';
			} 
			//$this->number_required.' and '. ($this->percent_successful*100).'%':
			//$this->number_required. ' out of last ';
		} else {
			if ($maxLastData) {
				$txt = ' out of last ' . $maxLastData . ' occurrences';
			}
		}
		$reqText = 'Required ' . $this->number_required . $txt;
		
		return $this->get_name() . ', '. $reqText;
	}
	
	// well, this is kind of gross, but I'd rather have all the logic in one place
	public function getGoalSQL() {
		$tables = "";
		$clause = "";
		$ages = $this->goalSet->getAllStartAges(false);
		$category = $this->def->category;
		$group_name = $this->def->group_name;

		switch ($category) {
			case "Ages":
				if(isset($ages[$group_name])) {
					$start = $ages[$group_name];
				} else {
					$start = 0;
				}

				// not sure how else to handle these, since there are slight variations
				switch ($group_name) {
					case "toddler":
						$end = $ages['preschooler'];
						break;
					case "preschooler":
						$end = $ages['school_age'];
						break;
					case "school_age":
						$end = $ages['adolescent'];
						break;
					case "adolescent":
						$end = $ages['adult'];
						break;
					case "adult":
						$end = $ages['geriatric'];
						break;
					case "geriatric":
						$end = 1000;
						break;
					default:
						$end = 1000;
				}
                $clause .= "AND P.age >= $start ";
                $clause .= "AND P.age < $end ";
				
				// ok, now set the special ones
				switch ($group_name) {
					case "newborn":
						$end = $ages['infant'];
						$clause = "AND (P.months < $end OR P.months IS NULL) ".
							  "AND (P.age < 1 OR P.age IS NULL) ".
                              "AND NOT (P.age IS NULL AND P.months IS NULL)";
						break;
					case "infant":
						$end = $ages['toddler'];
						$clause = "AND P.months >= $start AND (P.age < $end OR P.age IS NULL) ";
						break;
					case "pediatric":
						$end = $ages['adult'];
						$clause = "AND IF(P.age IS NULL, IF(P.months IS NULL, NULL, 0), P.age) < $end ";
						break;
				}
				break;
			case "Complaints":
				switch ($this->def->id) {
					// Breathing Problems - Adult
					case 137:
						$clause .= "AND P.age >= ".$ages['adult']." ";
						$clause .= "AND P.age < 1000 ";
						break;
					// Breathing Problems - Peds
					case 138:
						$clause .= "AND (P.age < ".$ages['adult']." OR (P.age IS NULL AND P.months IS NOT NULL)) ";
						break;
				}
				$tables .= ", fisdap2_patients_complaints C ";
				$clause .= "AND P.id = C.patient_id ".
					"AND C.complaint_id = ".$group_name." ";
				break;
			case "Hours":
				switch ($this->def->id) {
					// Total Field Hours
					case 125:
						$clause .= "AND S.type = 'field' ";
						break;

					// Total Clinical Hours
					case 105:
						$clause .= "AND S.type = 'clinical' ";
						break;
				}

				break;
			case "Impressions":
				$impressions = Impression::getIdsByType($group_name, 'string');
				$clause .= "AND (P.primary_impression_id IN ($impressions) ".
					   "OR P.secondary_impression_id IN ($impressions)) ";
				break;
			case "Skills":
				switch ($this->get_name()) {
					case "Medications":
						$table = "fisdap2_meds AS SK";
						$clause .= "AND SK.medication_id != 25 ";
						break;
					case "Endotracheal Intubation":
						$table = "fisdap2_airways AS SK";
						$clause .= "AND SK.procedure_id IN (5, 6, 10) ".
							"AND SK.success = 1 ";
						break;
					case "Live Intubation":
						$table = "fisdap2_airways AS SK ";
						$clause .= "AND SK.procedure_id IN (5, 6, 10) ".
							"AND SK.success = 1 ".
							"AND SK.subject_id = 1 ";
						break;
					case "Ventilations":
						$table = "fisdap2_airways AS SK";
						$clause .= "AND SK.procedure_id IN (28) ".
							"AND SK.success = 1 ";
						break;
					case "IVs":
						$table = "fisdap2_ivs AS SK";
						$clause .= "AND SK.procedure_id IN (1, 8) ";
						$clause .= "AND SK.success = 1 ";
						break;
				}
				$tables .= ", $table ".
					"LEFT JOIN fisdap2_runs R ".
					"ON SK.run_id = R.id ";
				$clause .= "AND SK.performed_by = 1 ";
				break;
			case "Team Lead":
				$clause .= "AND P.team_lead = 1 ";
				break;
		}
		
		if ($this->team_lead) {
			$team_lead = "AND P.team_lead = 1 ";
		} else {
			$team_lead = "";
		}
		if ($this->interview) {
			$clause .= "AND P.interview = 1 ";
		}
		if ($this->exam) {
			$clause .= "AND P.exam = 1 ";
		}
		return array('tables' => $tables, 'clause' => $clause, 'team_lead' => $team_lead);
	}
}
?>
