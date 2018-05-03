<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Fisdap\EntityUtils;


/**
 * Quick-add Lab Skill
 *
 * @Entity
 * @Table(name="fisdap2_lab_skills")
 * @HasLifecycleCallbacks
 */
class LabSkill extends Skill
{
	const viewScriptName = "labskill";

    /**
	 * @var integer
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;

    /**
     * @ManyToOne(targetEntity="LabAssessment")
     */
    protected $procedure;

	/**
     * @Column(type="boolean", nullable=true)
     */
    protected $success;

	public function init()
	{
		$this->subject = EntityUtils::getEntity('Subject', 1);
	}

	public function set_procedure($value)
	{
		$this->procedure = self::id_or_entity_helper($value, 'LabAssessment');
	}

	public function getViewScriptName()
	{
		return self::viewScriptName;
	}

	public function getProcedureText($html=true){

		$performedText = ($this->performed_by)?'Performed':'Observed';

		$details = array();

		if ($html) {
			$line1 = "<span class='summary-header {$this->shift->type}'>$this->procedure->name ($performedText)</span><br />";
			$line2 = "<span class='summary-details'>" . implode("; ", $details) . "</span>";

			return $line1 . $line2;
		} else {
			$line1 = "$this->procedure->name ($performedText)\n";
			if (count($details)) {
				$line2 = implode("; ", $details) . "\n";
			} else {
			    $line2 = "";
            }

			return ucwords(self::viewScriptName) . "\n" . $line1 . $line2 . "\n";
		}
	}

	public function getHookIds()
	{
		return array();
	}
}