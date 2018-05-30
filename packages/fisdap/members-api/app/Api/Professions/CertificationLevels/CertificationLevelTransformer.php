<?php namespace Fisdap\Api\Professions\CertificationLevels;

use Fisdap\Entity\CertificationLevel;
use Fisdap\Fractal\Transformer;

/**
 * Prepares certification level data for JSON output
 *
 * @package Fisdap\Api\Professions\CertificationLevels
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class CertificationLevelTransformer extends Transformer
{
    /**
     * @param CertificationLevel|array $certificationLevel
     *
     * @return array
     */
    public function transform($certificationLevel)
    {
        if ($certificationLevel instanceof CertificationLevel) {
            $certificationLevel = $certificationLevel->toArray();
        }

        return $certificationLevel;
    }
}
