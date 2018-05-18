<?php namespace User\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;


/**
 * Narrative
 * 
 * @Entity
 * @Table(name="fisdap2_narratives")
 */
class Narrative extends EntityBaseClass
{
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;

	/**
	 * @ManyToOne(targetEntity="ShiftLegacy")
	 * @JoinColumn(name="shift_id", referencedColumnName="Shift_id")
	 */
	protected $shift;

	/**
	 * @ManyToOne(targetEntity="Run")
	 */
	protected $run;

	/**
	 * @OneToOne(targetEntity="Patient")
	 */
	protected $patient;

	/**
	 * @ManyToOne(targetEntity="StudentLegacy", inversedBy="narratives", cascade={"persist"})
	 * @JoinColumn(name="student_id", referencedColumnName="Student_id")
	 */
	protected $student;

	/**
	 * @Column(type="boolean", nullable=true)
	 */
	protected $legacy_narrative = false;

	/**
	 * @var ArrayCollection
	 * @OneToMany(targetEntity="NarrativeSection", mappedBy="narrative", cascade={"persist", "remove"})
	 */
	protected $sections;

	public function init()
	{
		$this->sections = new ArrayCollection();
	}

	public function set_shift($value)
	{
		$this->shift = self::id_or_entity_helper($value, 'ShiftLegacy');
	}

	public function set_run($value)
	{
		$this->run = self::id_or_entity_helper($value, 'Run');
	}

	public function set_patient($value)
	{
		$this->patient = self::id_or_entity_helper($value, 'Patient');
	}

	public function set_student($value)
	{
		$this->student = self::id_or_entity_helper($value, 'StudentLegacy');
	}

	/**
	 * Add association between NarrativeSection and Narrative
	 *
	 * @param NarrativeSection $section
	 */
	public function addSection(NarrativeSection $section)
	{
		$this->sections->add($section);
		$section->narrative = $this;
	}


	public function getNarrativeSeed() {

		// if this narrative isn't associated with a patient, return an empty string
		if (!$this->patient) {
			return '';
		}

		$seed = '';
		//Do not include run info for clinical shifts
		if ($this->shift->type != 'clinical') {
			//Run Info
			$seed = "Team Info: ";

			if($this->patient->team_size){
				$seed .= "{$this->patient->team_size} members";
			}
			if(trim($this->patient->preceptor->first_name . " " . $this->patient->preceptor->last_name)){
				$seed .= ", including {$this->patient->preceptor->first_name} {$this->patient->preceptor->last_name},";
			}
			if (!$this->patient->team_lead) {
				$seed .= " not";
			}
			$seed .= " lead by the student.\n\n";
		}


		//Patient Info
		$seed .= "Patient Info:\n";
		$seed .= $this->patient->getSummaryLine();

		$moi = $this->patient->getMechanismNames();
		$bps = array();
		foreach($this->patient->vitals as $vital){
			$bps[] = $vital->getBp();
		}

		$bps = implode(',', $bps);
		$seed .= "\nPrimary Impression: {$this->patient->primary_impression->name}";
		$seed .= "\nSecondary Impression: {$this->patient->secondary_impression->name}";

		if(trim($moi) != ''){
			$seed .= "\nMOI: $moi ";
		}

		if(trim($bps) != ''){
			$seed .= "\nBP: $bps";
		}

		$seed .= "\n\n";

		$skills = EntityUtils::getRepository('Patient')->getSkillsByPatient($this->patient->id);

		foreach ($skills as $skill) {
			$seed .= $skill->getProcedureText(false);
		}

		return $seed;
	}

	public function getProcedureText($html=true)
	{
		$text = '';
		foreach ($this->orderSections($this->sections) as $section) {
			if ($html) {
				$text .=  "<h4 class='narrative-section-header'>".$section->definition->name.":</h4>";
				$text .=  "<div class='narrative-section'>".nl2br($section->section_text)."</div>";
			} else {
				$text .=  $section->definition->name.":\n";
				$text .=  $section->section_text."\n\n";
			}
		}

		return $text;
	}

	/**
	 * Get the Hook Ids for a narrative
	 */
	public function getHookIds()
	{
		return array();
	}

	/**
	 * Order the sections by defined section order
	 */
	public static function orderSections($sections)
	{
		$sorted = array();
		foreach ($sections as $section) {
			$sorted[] = $section;
		}
		@usort($sorted, array('self', 'sortSectionsBySectionOrder'));
		return $sorted;
	}

	public static function sortSectionsBySectionOrder($a, $b){
		if($a->definition->section_order == $b->definition->section_order){
			return ($a->definition->id < $b->definition->id ? -1 : 1);
		}

		return ($a->definition->section_order < $b->definition->section_order ? -1 : 1);
	}

	/**
	 * true if all the sections are blank
	 */
	public function isBlank()
	{
		$blank = TRUE;
		foreach ($this->sections as $section) {
			if (trim($section->section_text)) {
				$blank = FALSE;
			}
		}

		return $blank;
	}

	public function getSections()
	{
		return $this->sections;
	}

	public function getSectionsArray()
	{
		$sections = [];
		foreach ($this->getSections() as $section => $body) {
			$sections[$section] = $body;
		}

		return ['sections' => $sections];
	}
}
