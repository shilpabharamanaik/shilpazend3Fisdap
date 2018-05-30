<?php namespace Fisdap\Api\Programs\Settings\Transformation;

use Fisdap\Entity\ProgramSettings;
use Fisdap\Fractal\Transformer;

/**
 * Prepares program settings data for JSON output
 *
 * @package Fisdap\Api\Programs\Settings\Transformation
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ProgramSettingsTransformer extends Transformer
{
    /**
     * @param ProgramSettings|array $programSettings
     *
     * @return array
     */
    public function transform($programSettings)
    {
        if ($programSettings instanceof ProgramSettings) {
            $programSettings = $programSettings->toArray();
        }

        if (!isset($programSettings['uuid'])) {
            $this->removeFields(['uuid'], $programSettings);
        }

        return $programSettings;
    }
}
