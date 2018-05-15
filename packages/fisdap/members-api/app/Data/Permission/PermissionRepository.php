<?php namespace Fisdap\Data\Permission;

use Fisdap\Data\Repository\Repository;
use Fisdap\Entity\Permission;

/**
 * Interface PermissionRepository
 *
 * @package Fisdap\Data\Permission
 */
interface PermissionRepository extends Repository
{
    /**
     * @param array $ids
     *
     * @return Permission|Permission[]|null
     */
    public function getById(array $ids);

    /**
     * Grab all the permission objects unless we exclude some
     *
     * @param integer $exclude configuration code of products to include
     * @param bool    $include
     *
     * @return array of \Fisdap\Entity\Product
     */
    public function getPermissions($exclude = 0, $include = false);

    /**
     * Get permission entities based on given category
     *
     * @param integer|\Fisdap\Entity\PermissionCategory $cat
     *
     * @return array
     */
    public function getPermissionsByCategory($cat);

    /**
     * Get array of permission category entities
     *
     * @return array
     */
    public function getPermissionCategories();

    /**
     * Get the bit value representing having all permissions in the system
     *
     * @return integer
     */
    public function getAllPermissionsBits();

    /**
     * Use Doctrine query builder to return an array of Permission to be used by getFormOptions()
     * @return array $results
     */
    public function getPermissionNames();

    /**
     * Organize array returned by getPermissionNames() into a new array which can be used in a Zend form.
     *
     * @param string $keyField the permission property to be used for the keys of the returned array
     * @param string $valueField the permission property to be used for the values of the returned array
     *
     * @return array $results
     */
    public function getFormOptions($keyField = "id", $valueField = "name");

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
    public function getFormOptionsGroupedByCategory($keyField = "id", $valueField = "name", $categoryNameField = 'category_name');
}
