<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;

/**
 * Entity for scenarios.
 *
 * @Entity
 * @Table(name="fisdap2_scenario_skills")
 */
class ScenarioSkill extends EntityBaseClass
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @ManyToOne(targetEntity="Scenario")
     */
    protected $scenario;

    /**
     * @Column(type="integer")
     */
    protected $skill_id;
    
    /**
     * @Column(type="string")
     */
    protected $skill_type;
    
    /**
     * @Column(type="boolean")
     */
    protected $is_als = false;
    
    /**
     * @Column(type="integer")
     */
    protected $priority = 2;
    // 1 = low, 2 = normal, 3 = high
    
    /**
     * This function takes the skill_type and the procedure for that skill
     * and determines whether the is_als field should be true or false.
     */
    public static function getSkillAlsState($skillType, $skillId)
    {
        $skill = EntityUtils::getEntity($skillType, $skillId);
        
        $validBlsSkills = array();
        
        $nameField = "";
        
        switch ($skillType) {
            case "Airway":
                $validBlsSkills = array(
                     "Combitube", "EOA/EGTA", "Endotracheal Suctioning",
                     "Intubation confirmation - esophageal bulb", "KING LT",
                     "Manual ventilation", "Nasopharyngeal airway",
                     "Nebulizer treatment",
                     "Obstruction cleared (Heimlich or other)",
                    "Oropharyngeal airway", "Suction"
                );
                $nameField = $skill->procedure->name;
                break;
            case "CardiacIntervention":
                $validBlsSkills = array(
                    "Chest Compressions", "Carotid Sinus Massage",
                    "Valsalva's Maneuver", "Defibrillation"
                );
                $nameField = $skill->procedure->name;
                break;
            case "Iv":
                $validBlsSkills = array(
                    "Discontinue venous access", "IV", "IO"
                );
                $nameField = $skill->procedure->name;
                break;
            case "Med":
                $validBlsSkills = array(
                    "Activated Charcoal", "Albuterol (Proventil)",
                    "Aspirin (Bayer)", "Dextrose (D50)",
                    "Epinephrine 1:1000", "Glucagon (GlucaGen)",
                    "Naloxone (Narcan)",
                    "Nitroglycerin (Nitrostat, Nitro-Bid)",
                    "Nitrous Oxide (Nitronox)", "Oral Glucose (Glutose)",
                    "Oxygen"
                );
                $nameField = $skill->medication->name;
                break;
            case "OtherIntervention":
                $validBlsSkills = array(
                    "Bandaging", "C-Spine Immobilization",
                    "Decontamination", "Extrication", "Hospital Notify",
                    "Joint Immobilization", "Kendrick Extrication Device",
                    "Long Bone Immobilization", "Long board",
                    "MAST", "MD consult", "Orthostatic blood pressure",
                    "Pulse Oximetry", "Rescue"
                );
                $nameField = $skill->procedure->name;
                break;
        }
        
        // Since we're determining ALS state, if it's in the BLS skills list, return false, otherwise return true.
        
        if (in_array($nameField, $validBlsSkills)) {
            return false;
        }
        
        return true;
    }
}
