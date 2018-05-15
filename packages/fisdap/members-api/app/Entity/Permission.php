<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Fisdap\EntityUtils;

/**
 * Entity class for Permissions.
 *
 * @Entity(repositoryClass="Fisdap\Data\Permission\DoctrinePermissionRepository")
 * @Table(name="fisdap2_permission")
 */
class Permission extends Enumerated
{
    /**
     * @var int
     * @Column(type="integer");
     */
    protected $bit_value;

    /**
     * @var string
     * @Column(type="string", length=512);
     */
    protected $description;

    /**
     * @var PermissionCategory
     * @ManyToOne(targetEntity="PermissionCategory")
     */
    protected $category;


    /**
     * This function provides an easy way to get an array to use in dropdown
     * select boxes.
     *
     * @param Boolean $na          Determines whether or not to include an "N/A" option
     *                             in the list. Defaults to false.
     * @param Boolean $sort        Determines whether or not to sort the returning list/
     *                             Defaults to true.
     * @param string  $displayName The field name that we should output, defaults to "name"
     * @param integer $categoryId  The ID of the category of permissions
     *
     * @return Array containing the requested list of entities, with the index
     * being the ID of the entity, and the value at that index the name field of
     * the entity.
     */
    public static function getFormOptions($na = false, $sort = true, $displayName = "name", $categoryId = null)
    {
        $options = [];
        $repo = EntityUtils::getEntityManager()->getRepository(get_called_class());

        if ($categoryId) {
            $results = $repo->findByCategory($categoryId);
        } else {
            $results = $repo->findAll();
        }

        foreach ($results as $result) {
            if ($result->id != -1) {
                $tempOptions[$result->bit_value] = $result->$displayName;
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


    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }


    /**
     * @return PermissionCategory
     */
    public function getCategory()
    {
        return $this->category;
    }


    /**
     * @return int
     */
    public function getBitValue()
    {
        return $this->bit_value;
    }


    public function toArray()
    {
        return [
            'id'          => $this->getId(),
            'name'        => $this->getName(),
            'description' => $this->getDescription(),
            'bit_value'   => $this->getBitValue()
        ];
    }
}
