<?php namespace Fisdap\Api\Transformation;

use Fisdap\Entity\Enumerated;
use League\Fractal\TransformerAbstract;

/**
 * Class EnumeratedTransformer
 *
 * @package Fisdap\Api\Transformation
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class EnumeratedTransformer extends TransformerAbstract
{
    /**
     * @param $enum
     *
     * @return array
     */
    public function transform($enum)
    {
        if ($enum instanceof Enumerated) {
            $enum = $enum->toArray();
        }
        
        return $enum;
    }
}
