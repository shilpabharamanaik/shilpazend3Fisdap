<?php namespace Fisdap\Api\Programs\Settings\Transformation;

use Fisdap\Entity\ProgramMedType;
use Fisdap\Fractal\Transformer;

/**
 * Prepares program settings data for JSON output
 *
 * @package Fisdap\Api\Programs\Settings\Transformation
 * @author  Nick Karnick <nkarnick>
 */
final class ProgramMedTypeTransformer extends Transformer
{
    /**
     * @param ProgramMedType|array $programMedType
     *
     * @return array
     */
    public function transform($programMedType)
    {
        if ($programMedType instanceof ProgramMedType) {
            $programMedType = $programMedType->toArray();
        }

        return $programMedType;
    }
}
