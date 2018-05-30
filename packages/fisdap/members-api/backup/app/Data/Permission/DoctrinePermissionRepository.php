<?php namespace Fisdap\Data\Permission;

use Fisdap\Data\Repository\DoctrineRepository;

/**
 * Class DoctrinePermissionRepository
 *
 * @package Fisdap\Data\Permission
 */
class DoctrinePermissionRepository extends DoctrineRepository implements PermissionRepository
{
    /**
     * Grab all the permission objects unless we exclude some
     *
     * @param integer $exclude configuration code of products to include
     * @param bool    $include
     *
     * @return array of \Fisdap\Entity\Product
     */
    public function getPermissions($exclude = 0, $include = false)
    {
        $results = $this->findAll();
        if ($include) {
            $includedResults = array();
        }

        //If we have products to exclude go thru them, because DQL is stupid
        if ($exclude > 0) {
            foreach ($results as $i => $result) {
                if ($result->bit_value & $exclude) {
                    unset($results[$i]);
                    if ($include) {
                        $includedResults[] = $result;
                    }
                }
            }
        }

        if ($include) {
            return $includedResults;
        } else {
            return $results;
        }
    }

    /**
     * Get permission entities based on given category
     *
     * @param integer|\Fisdap\Entity\PermissionCategory $cat
     *
     * @return array
     */
    public function getPermissionsByCategory($cat)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('perm')
            ->from('\Fisdap\Entity\Permission', 'perm')
            ->where('perm.category = ?1')
            ->setParameter(1, $cat);

        return $qb->getQuery()->getResult();
    }

    /**
     * Get array of permission category entities
     *
     * @return array
     */
    public function getPermissionCategories()
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('category')
            ->from('\Fisdap\Entity\PermissionCategory', 'category');

        return $qb->getQuery()->getResult();
    }

    /**
     * Get the bit value representing having all permissions in the system
     *
     * @return integer
     */
    public function getAllPermissionsBits()
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('SUM(p.bit_value)')
            ->from('\Fisdap\Entity\Permission', 'p');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Use Doctrine query builder to return an array of Permission to be used by getFormOptions()
     * @return array $results
     */
    public function getPermissionNames()
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('perm.id, perm.name, perm.bit_value, cat.name as category_name')
            ->from('\Fisdap\Entity\Permission', 'perm')
            ->join('perm.category', 'cat');

        $qb->orderBy('perm.id', 'ASC');

        $results = $qb->getQuery()->getResult();

        return $results;
    }

    /**
     * Organize array returned by getPermissionNames() into a new array which can be used in a Zend form.
     *
     * @param string $keyField the permission property to be used for the keys of the returned array
     * @param string $valueField the permission property to be used for the values of the returned array
     *
     * @return array $results
     */
    public function getFormOptions($keyField = "id", $valueField = "name")
    {
        $rawPerms = $this->getPermissionNames();
        $formOptions = array();
        foreach ($rawPerms as $perm) {
            $formOptions[$perm[$keyField]] = $perm[$valueField];
        }
        return $formOptions;
    }

    /**
     * Organize array returned by getPermissionNames() into a new array which can be used in a Zend form but organizes
     * optgroups based on permission category.
     *
     * @param string $keyField   the permission property to be used for the keys of the returned array
     * @param string $valueField the permission property to be used for the values of the returned array
     * @param string $categoryNameField the name of the field to group by
     *
     * @return array $results
     */
    public function getFormOptionsGroupedByCategory($keyField = "id", $valueField = "name", $categoryNameField = 'category_name')
    {
        $rawPerms = $this->getPermissionNames();
        $formOptions = array();
        foreach ($rawPerms as $perm) {
            $formOptions[$perm[$categoryNameField]][$perm[$keyField]] = $perm[$valueField];
        }
        return $formOptions;
    }
}
