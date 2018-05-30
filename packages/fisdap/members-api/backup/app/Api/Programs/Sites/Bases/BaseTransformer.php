<?php namespace Fisdap\Api\Programs\Sites\Bases;

use Fisdap\Entity\BaseLegacy;
use League\Fractal\TransformerAbstract as Transformer;

/**
 * Prepares base data for JSON output
 *
 * @package Fisdap\Api\Programs\Sites\Bases
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class BaseTransformer extends Transformer
{
    /**
     * @param array $base
     *
     * @return array
     */
    public function transform($base)
    {
        if ($base instanceof BaseLegacy) {
            $base = $base->toArray();
        }
        return $base;
    }
}
