<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * Site Type
 *
 * @Entity
 * @Table(name="fisdap2_site_type")
 */
class SiteType extends Enumerated
{
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @var string
     * @Column(type="string")
     */
    protected $name;
    
    public static function getCapitalizedFormOptions()
    {
        $capitalized_options = array();
        $options = parent::getFormOptions();
        foreach ($options as $key => $option) {
            $capitalized_options[$key] = ucfirst($option);
        }
        return $capitalized_options;
    }
}
