<?php namespace Fisdap\Api\Timezones;

use Fisdap\Entity\Timezone;
use Fisdap\Fractal\Transformer;

/**
 * Prepares timezone data for JSON output
 *
 * @package Fisdap\Api\Timezones
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class TimezoneTransformer extends Transformer
{
    /**
     * @param Timezone|array $timezone
     *
     * @return array
     */
    public function transform($timezone)
    {
        if ($timezone instanceof Timezone) {
            $timezone = $timezone->toArray();
        }
        
        return $timezone;
    }
}
