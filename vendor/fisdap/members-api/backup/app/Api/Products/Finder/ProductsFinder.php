<?php namespace Fisdap\Api\Products\Finder;

use Fisdap\Api\Products\Queries\Specifications\MatchingUserContext;
use Fisdap\Api\Products\Queries\Specifications\UserContextProducts;
use Fisdap\Api\Products\Queries\Specifications\WithProgramProfession;
use Fisdap\Api\Products\SerialNumbers\Exception\ProductConfigurationCalculationFailure;
use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Data\Product\Package\ProductPackageRepository;
use Fisdap\Data\Product\ProductRepository;
use Fisdap\Data\User\UserContext\UserContextRepository;
use Fisdap\Entity\Product;
use Fisdap\Entity\ProductPackage;
use Happyr\DoctrineSpecification\Spec;
use Illuminate\Support\Collection;

/**
 * Service for retrieving product names and determining user/role access
 *
 * @package Fisdap\Api\Products
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ProductsFinder implements FindsProducts
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var ProductPackageRepository
     */
    private $productPackageRepository;

    /**
     * @var UserContextRepository
     */
    private $userContextRepository;


    /**
     * @param ProductRepository        $repository
     * @param ProductPackageRepository $productPackageRepository
     * @param UserContextRepository    $userContextRepository
     */
    public function __construct(
        ProductRepository $repository,
        ProductPackageRepository $productPackageRepository,
        UserContextRepository $userContextRepository
    ) {
        $this->productRepository = $repository;
        $this->productPackageRepository = $productPackageRepository;
        $this->userContextRepository = $userContextRepository;
    }


    /**
     * @inheritdoc
     */
    public function getUserContextProductNames($userContextId)
    {
        $products = $this->productRepository->match(
            new UserContextProducts($userContextId),
            Spec::asArray()
        );

        if (empty($products)) {
            throw new ResourceNotFound("No products found for userContextId '$userContextId'");
        }

        $names = array_pluck($products, 'name');

        return $names;
    }


    /**
     * @inheritdoc
     */
    public function getUserContextProducts($userContextId)
    {
        return Collection::make($this->productRepository->match(
            Spec::andX(
                new MatchingUserContext,
                new WithProgramProfession,
                Spec::eq('id', $userContextId, 'userContext')
            )
        ));
    }


    /**
     * @inheritdoc
     */
    public function getUniqueProductsFromPackages(array $packages)
    {
        $packages = Collection::make($packages);

        return $packages->filter(function ($package) {
            return $package instanceof ProductPackage;
        })->flatMap(function (ProductPackage $package) {
            return $this->productRepository->getProductsMatchingConfigAndCertLevel(
                $package->getConfiguration(),
                $package->getCertificationLevel()
            );
        })->unique(function (Product $product) {
            return $product->getId();
        });
    }

    
    /**
     * @inheritdoc
     */
    public function getConfigurationValueForProductsOrPackages(
        array $productIds = null,
        array $productPackageIds = null
    ) {
        $products = Collection::make();
        
        if (is_array($productIds)) {
            $products = $products->merge($this->productRepository->getById($productIds));
        }
        
        if (is_array($productPackageIds)) {
            $products = $products->merge(
                $this->getUniqueProductsFromPackages($this->productPackageRepository->getById($productPackageIds))
            );
        }

        $configuration = $products->unique(function (Product $product) {
            return $product->getId();
        })->sum(function (Product $product) {
            return $product->getConfiguration();
        });

        if ($configuration === 0) {
            throw new ProductConfigurationCalculationFailure(
                'Unable to calculate configuration for specified products or packages'
            );
        }

        return $configuration;
    }
    

    /**
     * @inheritdoc
     */
    public function hasProduct($productName, array $userProducts)
    {
        return in_array($productName, array_values($userProducts));
    }


    /**
     * @inheritdoc
     */
    public function findProductsAndPackagesByIsbns(array $isbns)
    {
        $products = $this->productRepository->findBy(['ISBN' => $isbns]);

        $packages = $this->productPackageRepository->findBy(['package_ISBN' => $isbns]);

        $productsOrPackages = array_merge($products, $packages);

        if (empty($productsOrPackages)) {
            throw new ResourceNotFound('No products or packages were found for ISBN(s): ' . implode(', ', $isbns));
        }

        return $productsOrPackages;
    }
}
