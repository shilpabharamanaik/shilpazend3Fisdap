<?php namespace Fisdap\Api\Shifts\Patients\Procedures\Transformation;

use Fisdap\Entity\IvProcedure;
use League\Fractal\TransformerAbstract as Transformer;

/**
 * Prepares iv procedure data for JSON output
 *
 * @package Fisdap\Api\Shifts\Patients\Procedures
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class IvProcedureTransformer extends Transformer
{
    /**
     * @param array $ivProcedure
     *
     * @return array
     */
    public function transform($ivProcedure)
    {
        if ($ivProcedure instanceof IvProcedure) {
            return $ivProcedure->toArray();
        }

        return $ivProcedure;
    }
}
