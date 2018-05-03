<?php namespace Fisdap\Api\Programs\Settings\Transformation;

use Fisdap\Entity\ProgramOtherProcedure;
use Fisdap\Fractal\Transformer;

/**
 * Prepares program settings data for JSON output
 *
 * @package Fisdap\Api\Programs\Settings\Transformation
 * @author  Nick Karnick <nkarnick>
 */
final class ProgramOtherProcedureTransformer extends Transformer
{
    /**
     * @param ProgramOtherProcedure|array $programOtherProcedure
     *
     * @return array
     */
    public function transform($programOtherProcedure)
    {
        if ($programOtherProcedure instanceof ProgramOtherProcedure) {
            $programOtherProcedure = $programOtherProcedure->toArray();
        }

        return $programOtherProcedure;
    }
}
