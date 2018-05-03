<?php namespace Fisdap\Api\Shifts\Patients\Procedures\Transformation;

use Fisdap\Entity\AirwayProcedure;
use League\Fractal\TransformerAbstract as Transformer;

/**
 * Prepares airway procedure data for JSON output
 *
 * @package Fisdap\Api\Shifts\Patients\Procedures
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class AirwayProcedureTransformer extends Transformer
{
    /**
     * @param array $airwayProcedure
     *
     * @return array
     */
    public function transform($airwayProcedure)
    {
        if ($airwayProcedure instanceof AirwayProcedure) {
            return $airwayProcedure->toArray();
        }

        return $airwayProcedure;
    }
}
