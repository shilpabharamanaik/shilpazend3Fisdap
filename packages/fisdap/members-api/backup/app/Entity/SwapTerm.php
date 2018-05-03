<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Fisdap\EntityUtils;


/**
 * Swap Term
 * 
 * @Entity
 * @Table(name="fisdap2_swap_terms")
 */
class SwapTerm extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @var ShiftRequest
     * @ManyToOne(targetEntity="ShiftRequest", inversedBy="swap_terms")
     */
    protected $request;
        
    /**
     * @var TermType
     * @ManyToOne(targetEntity="TermType")
     */
    protected $term_type;
    
    /**
     * @var array
     * @Column(type="array")
     */
    protected $value;
    
    public function set_term_type($value)
    {
        $this->term_type = self::id_or_entity_helper($value, 'TermType');
    }
    
    public function set_value($value)
    {
        if (is_array($value)) {
            $this->value = $value;
        } else {
            $this->value = array($value);
        }
    }
    
    /**
     * Returns a string containing a description of these terms
     */
    public function getDescription() {
	if ($this->term_type->name == 'duration') {
            if ($this->value[0] == 1) {
                return "1 hour long";
            }
            return $this->value[0]." hours long";
	}
        
        $values = array();
        foreach ($this->value as $id) {
            $entity = EntityUtils::getEntity($this->term_type->entity_name, $id);
            $values[] = ucfirst($entity->name);
        }
	return implode(', ', $values);
    }

}
