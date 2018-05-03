<?php namespace Fisdap\Api\Programs\Settings\Transformation;

use Fisdap\Entity\ProgramAirwayProcedure;
use Fisdap\Fractal\Transformer;

/**
 * Prepares program settings data for JSON output
 *
 * @package Fisdap\Api\Programs\Settings\Transformation
 * @author  Nick Karnick <nkarnick>
 */
final class ProgramAirwayProcedureTransformer extends Transformer
{
    /**
     * @param ProgramAirwayProcedure|array $programAirwayProcedure
     *
     * @return array
     */
    public function transform($programAirwayProcedure)
    {
        if ($programAirwayProcedure instanceof ProgramAirwayProcedure) {
            $programAirwayProcedure = $programAirwayProcedure->toArray();
        }

        return $programAirwayProcedure;
    }
}
