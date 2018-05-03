<?php namespace Fisdap\Api\Shifts\Patients\Procedures\Transformation;

use Fisdap\Entity\CardiacProcedure;
use League\Fractal\TransformerAbstract as Transformer;

/**
 * Prepares cardiac procedure data for JSON output
 *
 * @package Fisdap\Api\Shifts\Patients\Procedures
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class CardiacProcedureTransformer extends Transformer
{
    /**
     * @param array $cardiacProcedure
     *
     * @return array
     */
    public function transform($cardiacProcedure)
    {
        if ($cardiacProcedure instanceof CardiacProcedure) {
            return $cardiacProcedure->toArray();
        }

        return $cardiacProcedure;
    }
}
