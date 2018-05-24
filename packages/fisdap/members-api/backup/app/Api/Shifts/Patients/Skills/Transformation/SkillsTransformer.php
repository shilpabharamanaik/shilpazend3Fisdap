<?php namespace Fisdap\Api\Shifts\Patients\Skills\Transformation;


use Fisdap\Entity\Skill;
use Fisdap\Fractal\Transformer;

/**
 * Class SkillsTransformer
 * @package Fisdap\Api\Shifts\Patients\Skills\Transformation
 * @author  Isaac White <isaac.white@ascendlearning.com>
 */
final class SkillsTransformer extends Transformer
{
    /**
     * @param $patient
     *
     * @return array
     */
    public function transform($skill)
    {
        if ($skill instanceof Skill) {
            $skill = $skill->toArray();
        }

        // This looks pointless, I know, but mobile was complaining that the API was returning 0 when
        // size wasn't sent. So I'm explicitly forcing a null value instead of the default integer for
        // and empty value.
        if (isset($skill['size']) && $skill['size'] == null) {
            $skill['size'] = null;
        }

        return $skill;
    }
}

