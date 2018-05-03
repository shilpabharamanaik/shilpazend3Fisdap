<?php namespace Fisdap\Api\Products\Finder;

use Fisdap\Api\Products\SerialNumbers\Exception\ProductConfigurationCalculationFailure;
use Fisdap\Entity\Product;
use Fisdap\Entity\ProductPackage;
use Illuminate\Support\Collection;

/**
 * Contract for retrieving product names and determining user/role access
 *
 * @package Fisdap\Api\Products
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface FindsProducts
{
    /**
     * @param int $userContextId
     *
     * @return array
     * @throws \Fisdap\Api\Queries\Exceptions\ResourceNotFound
     */
    public function getUserContextProductNames($userContextId);


    /**
     * @param int $userContextId
     *
     * @return Collection|Product[]
     */
    public function getUserContextProducts($userContextId);


    /**
     * @param ProductPackage[] $packages
     *
     * @return Collection|Product[]
     */
    public function getUniqueProductsFromPackages(array $packages);
    
    
    /**
     * @param array                    $productIds
     * @param array                    $productPackageIds
     *
     * @return int
     * @throws ProductConfigurationCalculationFailure
     */
    public function getConfigurationValueForProductsOrPackages(
        array $productIds = null,
        array $productPackageIds = null
    );
    
    
    /**
     * @param string $productName
     * @param array  $userProducts
     *
     * @return bool
     */
    public function hasProduct($productName, array $userProducts);


    /**
     * @param string[] $isbns
     *
     * @return Product[]|ProductPackage[]
     */
    public function findProductsAndPackagesByIsbns(array $isbns);
}
