<?php namespace Fisdap\Api\Programs\Types;

use Fisdap\Entity\ProgramLegacy;
use Fisdap\Entity\ProgramTypeLegacy;
use Fisdap\Fractal\Transformer;


/**
 * Prepares program type data for JSON output
 *
 * @package Fisdap\Api\Programs\Types
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ProgramTypeTransformer extends Transformer
{
    /**
     * @param ProgramTypeLegacy|array $programType
     *
     * @return array
     */
    public function transform($programType)
    {
        if ($programType instanceof ProgramTypeLegacy) {
            $programType = $programType->toArray();
        }

        return $programType;
    }
} 