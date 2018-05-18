<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;

/**
 * Entity class for Certification Levels.
 *
 * @Entity(repositoryClass="Fisdap\Data\CertificationLevel\DoctrineCertificationLevelRepository")
 * @Table(name="fisdap2_certification_levels")
 */
class CertificationLevel extends Enumerated
{
    /**
     * @Column(type="string")
     */
    protected $description;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $abbreviation;

    /**
     * @ManyToOne(targetEntity="Profession", inversedBy="certifications", cascade={"detach"}, fetch="EAGER")
     */
    protected $profession;

    /**
     * @Column(type="integer") a configuration bitmask to disallow this certification from certain accounts
     */
    protected $configuration_blacklist;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $bit_value;

    /**
     * @var integer the order in which to display the cert levels
     * @Column(type="integer", nullable=true)
     */
    protected $display_order;

    /**
     * @var int
     * @Column(type="integer", nullable=true)
     */
    protected $default_program_length_days;


    /**
     * @return Profession
     */
    public function getProfession()
    {
        return $this->profession;
    }


    /**
     * @param Profession $profession
     */
    public function setProfession(Profession $profession)
    {
        $this->profession = $profession;
    }


    /**
     * @return int
     */
    public function getDefaultProgramLengthDays()
    {
        return $this->default_program_length_days;
    }


    /**
     * @param int $default_program_length_days
     */
    public function setDefaultProgramLengthDays($default_program_length_days)
    {
        $this->default_program_length_days = $default_program_length_days;
    }


    /**
     * This function provides an easy way to get an array to use in dropdown
     * select boxes.
     *
     * @param Boolean $na Determines whether or not to include an "N/A" option
     * in the list. Defaults to false.
     * @param Boolean $sort Determines whether or not to sort the returning list/
     * Defaults to true.
     * @param string $displayName The field name that we should output, defaults to "name"
     * @param Boolean $checkbox if true, will return the values a bit differently so that this function can be used to create checkboxes
     * Defaults to false.
     *
     * @return Array containing the requested list of entities, with the index
     * being the ID of the entity, and the value at that index the name field of
     * the entity.
     */
    public static function getFormOptions($na = false, $sort=true, $displayName = "name", $professionId = null, $checkbox = false)
    {
        //If profession is not provided, get it from the current user or default to EMS
        if (!$professionId) {
            $professionId = User::getLoggedInUser()->getCurrentProgram()->profession->id;
            $professionId = $professionId ? $professionId : 1;
        }
        
        $options = array();
        $repo = EntityUtils::getEntityManager()->getRepository(get_called_class());
        $results = $repo->findByProfession($professionId, array('display_order' => 'ASC'));
        
        foreach ($results as $result) {
            if ($result->id != -1) {
                if ($checkbox) {
                    $tempOptions[] = array(
                        "displayName" => $result->$displayName . " students",
                        "id" => $result->id,
                        "name" => $result->name
                    );
                } else {
                    $tempOptions[$result->id] = $result->$displayName;
                }
            }
        }
        
        if ($sort) {
            asort($tempOptions);
        }
        
        if ($na) {
            $options[0] = "N/A";
            foreach ($tempOptions as $id => $name) {
                $options[$id] = $name;
            }
        } else {
            $options = $tempOptions;
        }
        
        return $options;
    }
    
    public static function getAllFormOptions()
    {
        $options = array();
        $repo = EntityUtils::getEntityManager()->getRepository(get_called_class());
        $results = $repo->findAll();
        
        foreach ($results as $result) {
            if ($result->id != -1) {
                $prof = $result->profession->name;
                
                if (!$tempOptions[$prof]) {
                    $tempOptions[$prof] = array();
                }
                
                $tempOptions[$prof][$result->id] = $result->description;
            }
        }
        
        ksort($tempOptions);
        $sorted_options = array();
        
        foreach ($tempOptions as $prof_name => $sorted_prof) {
            $sorted_options[$prof_name] = array();
        }
        
        
        foreach ($tempOptions as $prof_name => $profession) {
            asort($profession);
            $sorted_options[$prof_name] = $profession;
        }
        
        return $sorted_options;
    }

    public static function getAll($asArray = false, $whichColumn='All')
    {
        $repo = EntityUtils::getEntityManager()->getRepository(get_called_class());

        return $repo->findAll();
    }

    public static function getAllByProfession($profession_id)
    {
        $repo = EntityUtils::getEntityManager()->getRepository(get_called_class());

        return $results = $repo->findByProfession($profession_id);
    }
    
    public static function getConfiguration($profession_id = null)
    {
        $repo = EntityUtils::getEntityManager()->getRepository(get_called_class());
        $results = $repo->getAllCertificationLevelInfo($profession_id);
        $config = 0;
        foreach ($results as $res) {
            $config = $config + $res['bit_value'];
        }
        
        return $config;
    }
}
