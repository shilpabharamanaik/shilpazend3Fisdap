<?php namespace Fisdap\Api\Shifts\Patients\Procedures\Transformation;

use Fisdap\Entity\OtherProcedure;
use League\Fractal\TransformerAbstract as Transformer;

/**
 * Prepares other procedure data for JSON output
 *
 * @package Fisdap\Api\Shifts\Patients\Procedures
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class OtherProcedureTransformer extends Transformer
{
    /**
     * @param array $otherProcedure
     *
     * @return array
     */
    public function transform($otherProcedure)
    {
        if ($otherProcedure instanceof OtherProcedure) {
            return $otherProcedure->toArray();
        }

        return $otherProcedure;
    }
}
