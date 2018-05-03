<?php namespace Fisdap\Api\Programs\Settings\Transformation;

use Fisdap\Entity\ProgramLabAssessment;
use Fisdap\Fractal\Transformer;

/**
 * Prepares program settings data for JSON output
 *
 * @package Fisdap\Api\Programs\Settings\Transformation
 * @author  Nick Karnick <nkarnick>
 */
final class ProgramLabAssessmentTransformer extends Transformer
{
    /**
     * @param ProgramLabAssessment|array $programLabAssessment
     *
     * @return array
     */
    public function transform($programLabAssessment)
    {
        if ($programLabAssessment instanceof ProgramLabAssessment) {
            $programLabAssessment = $programLabAssessment->toArray();
        }

        return $programLabAssessment;
    }
}
