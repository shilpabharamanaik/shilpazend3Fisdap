<?php namespace Fisdap\Api\Programs\Settings\Transformation;

use Fisdap\Entity\ProgramCardiacProcedure;
use Fisdap\Fractal\Transformer;

/**
 * Prepares program settings data for JSON output
 *
 * @package Fisdap\Api\Programs\Settings\Transformation
 * @author  Nick Karnick <nkarnick>
 */
final class ProgramCardiacProcedureTransformer extends Transformer
{
    /**
     * @param ProgramCardiacProcedure|array $programCardiacProcedure
     *
     * @return array
     */
    public function transform($programCardiacProcedure)
    {
        if ($programCardiacProcedure instanceof ProgramCardiacProcedure) {
            $programCardiacProcedure = $programCardiacProcedure->toArray();
        }

        return $programCardiacProcedure;
    }
}
