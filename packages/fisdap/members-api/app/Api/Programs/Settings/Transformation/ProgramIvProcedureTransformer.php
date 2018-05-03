<?php namespace Fisdap\Api\Programs\Settings\Transformation;

use Fisdap\Entity\ProgramIvProcedure;
use Fisdap\Fractal\Transformer;

/**
 * Prepares program settings data for JSON output
 *
 * @package Fisdap\Api\Programs\Settings\Transformation
 * @author  Nick Karnick <nkarnick>
 */
final class ProgramIvProcedureTransformer extends Transformer
{
    /**
     * @param ProgramIvProcedure|array $programIvProcedure
     *
     * @return array
     */
    public function transform($programIvProcedure)
    {
        if ($programIvProcedure instanceof ProgramIvProcedure) {
            $programIvProcedure = $programIvProcedure->toArray();
        }

        return $programIvProcedure;
    }
}
