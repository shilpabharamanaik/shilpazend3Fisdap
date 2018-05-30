<?php namespace Fisdap\Api\Products\Transformation;

use Fisdap\Entity\Product;
use Fisdap\Fractal\Transformer;

/**
 * Class ProductTransformer
 *
 * @package Fisdap\Api\Products\Transformation
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class ProductTransformer extends Transformer
{
    /**
     * @param Product|array $product
     *
     * @return array
     */
    public function transform($product)
    {
        if ($product instanceof Product) {
            $product = $product->toArray();
        }
        
        return $product;
    }
}
