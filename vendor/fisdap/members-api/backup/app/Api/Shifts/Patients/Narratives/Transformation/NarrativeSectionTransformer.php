<?php namespace Fisdap\Api\Shifts\Patients\Narratives\Transformation;


use Fisdap\Entity\NarrativeSection;
use Fisdap\Fractal\Transformer;

/**
 * Class NarrativeSectionTransformer
 * @package Fisdap\Api\Shifts\Patients\Narratives\Transformation
 * @author  Isaac White <isaac.white@ascendlearning.com>
 */
final class NarrativeSectionTransformer extends Transformer
{
    /**
     * @param $narrativeSection
     *
     * @return array
     */
    public function transform($narrativeSection)
    {
        if ($narrativeSection instanceof NarrativeSection) {
            $narrativeSection = $narrativeSection->toArray();
        }

        return $narrativeSection;
    }
}


