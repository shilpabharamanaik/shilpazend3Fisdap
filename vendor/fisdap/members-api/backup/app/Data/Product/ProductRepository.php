<?php namespace Fisdap\Data\Product;

use Fisdap\Data\Repository\Repository;
use Fisdap\Entity\Product;

/**
 * Interface ProductRepository
 *
 * @package Fisdap\Data\Product
 */
interface ProductRepository extends Repository
{
    /**
     * Grab all the products unless we exclude some
     *
     * @param integer $exclude  configuration code of products to exlude
     * @param bool    $include
     * @param boolean $listOnly only return a HTML formatted list of products
     * @param boolean $staff    get staff only accounts
     * @param boolean $readOnly is this being used to populate a form or read a list of products
     *
     * @param null    $professionId
     *
     * @return Product[]
     */
    public function getProducts(
        $exclude = 0,
        $include = false,
        $listOnly = false,
        $staff = false,
        $readOnly = true,
        $professionId = null
    );

    
    /**
     * Get all products based on a specifed category
     *
     * @param integer $category the category id
     *
     * @return Product[]
     */
    public function getProductsByCategory($category);

    
    /**
     * Get all products that have a moodle course
     *
     * @return Product[]
     */
    public function getProductsWithMoodleCourses();


    /**
     * @param int      $configuration
     * @param int|null $certificationLevelId
     *
     * @return Product[]
     */
    public function getProductsMatchingConfigAndCertLevel($configuration, $certificationLevelId = null);
    
    
    /**
     * Grab all the products in form-friendly format unless we exclude some
     *
     * @param int       $exclude     configuration code of products to exclude
     * @param bool      $include     configuration code of products to include
     * @param bool      $listOnly    only return a HTML formatted list of products
     * @param bool      $staff       get staff only products
     * @param bool      $readOnly    is this being used to populate a form or read a list of products
     * @param int|null  $professionId
     *
     * @return array
     */
    public function getFormOptions(
        $exclude = 0,
        $include = false,
        $listOnly = false,
        $staff = false,
        $readOnly = true,
        $professionId = null
    );
}
