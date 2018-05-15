<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Fisdap\EntityUtils;

/**
 * Order Type
 *
 * @Entity
 * @Table(name="fisdap2_order_type")
 */
class OrderType extends Enumerated
{
    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $description;
    
    /**
     * OVERRIDE of the parent class' getFormOptions method
     * The override makes a special exception for Order Type id = 3, which is a staff-only payment option
     *
     * @param Boolean $na Determines whether or not to include an "N/A" option
     * in the list. Defaults to false.
     * @param Boolean $sort Determines whether or not to sort the returning list/
     * Defaults to true.
     * @param string $displayName The field name that we should output, defaults to "name"
     * @param boolean $showStaff Whether to show staff-only payment options
     *
     * @return Array containing the requested list of entities, with the index
     * being the ID of the entity, and the value at that index the name field of
     * the entity.
     */
    public static function getFormOptions($na = false, $sort=true, $displayName = "name", $showStaff=false)
    {
        $options = array();
        $repo = EntityUtils::getEntityManager()->getRepository(get_called_class());
        $results = $repo->findAll();
        
        foreach ($results as $result) {
            // special exception made for order type ID==3, which is a staff-only option
            if ($result->id != -1 && ($showStaff || $result->id != 3)) {
                $tempOptions[$result->id] = $result->$displayName;
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
}
