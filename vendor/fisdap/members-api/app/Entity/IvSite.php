<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;


/**
 * Iv Site
 * 
 * @Entity
 * @Table(name="fisdap2_iv_site")
 */
class IvSite extends Enumerated
{
    /**
     * @Column(type="string", nullable=true)
     */
    protected $side;
	
	public static function getFormOptions($na = false, $sort = true, $displayName = 'name')
	{
		$options = array();
		
		$query = "SELECT DISTINCT i.name FROM \Fisdap\Entity\IvSite i";
		$results = \Fisdap\EntityUtils::getEntityManager()->createQuery($query)->getResult();
		
        if ($na) {
            $options[0] = "Unset";
        }
        
		foreach($results as $result) {
			$options[$result['name']] = $result['name'];
		}
		
		natcasesort($options);
		
		return $options;
	}

	public function toArray()
	{
		return [
			'id' => $this->getId(),
			'name' => $this->getName(),
			'side' => $this->side
		];
	}
}