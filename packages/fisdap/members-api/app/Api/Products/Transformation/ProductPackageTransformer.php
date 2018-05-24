<?php namespace Fisdap\Api\Products\Transformation;

use Fisdap\Entity\ProductPackage;
use Fisdap\Fractal\Transformer;


/**
 * Class ProductPackageTransformer
 *
 * @package Fisdap\Api\Products\Transformation
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class ProductPackageTransformer extends Transformer
{
    /**
     * @param ProductPackage|array $product
     *
     * @return array
     */
    public function transform($product)
    {
        if ($product instanceof ProductPackage) {
            $product = $product->toArray();
        }
        
        return $product;
    }
}