<?php namespace Fisdap\Api\Shifts\Patients\Narratives\Transformation;

use Fisdap\Entity\Narrative;
use Fisdap\Fractal\Transformer;

/**
 * Class NarrativeTransformer
 * @package Fisdap\Api\Shifts\Patients\Narratives\Transformation
 * @author  Isaac White <isaac.white@ascendlearning.com>
 */
final class NarrativeTransformer extends Transformer
{
    /**
     * @param $narrative
     *
     * @return array
     */
    public function transform($narrative)
    {
        if ($narrative instanceof Narrative) {
            $narrative = $narrative->toArray();
        }

        return $narrative;
    }
}
