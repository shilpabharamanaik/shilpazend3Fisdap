<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;


/**
 * Entity class for Student Portfolio options.
 * 
 * @Entity
 * @Table(name="fisdap2_portfolio_options")
 */
class PortfolioOptions extends EntityBaseClass
{
	/**
	 * @var integer
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;
	
	/**
	 * @ManyToOne(targetEntity="StudentLegacy", inversedBy="portfolioOptions")
	 * @JoinColumn(name="student_id", referencedColumnName="Student_id")
	 */
	protected $student;
	
	/**
	 * @Column(type="datetime", nullable=true)
	 */
	protected $all_requirements_date;
	
	/**
	 * @Column(type="boolean")
	 */
	protected $written_exams_completed = false;
	
	/**
	 * @Column(type="boolean")
	 */
	protected $practical_skill_sheets_completed = false;
	
	/**
	 * @Column(type="boolean")
	 */
	protected $clinical_tracking_records_completed = false;
	
	/**
	 * @Column(type="boolean")
	 */
	protected $field_internship_records_completed = false;
	
	/**
	 * @Column(type="boolean")
	 */
	protected $affective_learning_eval_completed = false;
	
	/**
	 * @Column(type="boolean")
	 */
	protected $student_counseling_completed = false;
	
	/**
	 * @Column(type="datetime", nullable=true)
	 */
	protected $passed_national_registry_date;
	
	/**
	 * @Column(type="boolean")
	 */
	protected $passed_national_registry_completed = false;
	
	/**
	 * @Column(type="datetime", nullable=true)
	 */
	protected $employed_date;
	
	/**
	 * @Column(type="boolean")
	 */
	protected $employed_date_completed = false;
	
	/**
	 * @Column(type="datetime", nullable=true)
	 */
	protected $employer_survey_date;
	
	/**
	 * @Column(type="boolean")
	 */
	protected $employer_survey_completed = false;
	
	/**
	 * @Column(type="datetime", nullable=true)
	 */
	protected $graduate_survey_date;
	
	/**
	 * @Column(type="boolean")
	 */
	protected $graduate_survey_completed = false;
	
	/**
	 * @Column(type="text")
	 */
	protected $completed_exams = "";
	
	public function get_formatted_date($field){
		if($this->$field == null){
			return '';
		}else{
			return $this->$field->format('m/d/Y');
		}
	}
}