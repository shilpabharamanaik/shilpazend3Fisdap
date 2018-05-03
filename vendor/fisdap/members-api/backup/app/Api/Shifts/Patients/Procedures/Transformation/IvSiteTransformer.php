<?php namespace Fisdap\Api\Shifts\Patients\Procedures\Transformation;

use Fisdap\Entity\IvSite;
use League\Fractal\TransformerAbstract as Transformer;

/**
 * Prepares iv site data for JSON output
 *
 * @package Fisdap\Api\Shifts\Patients\Procedures
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class IvSiteTransformer extends Transformer
{
    /**
     * @param array $ivSite
     *
     * @return array
     */
    public function transform($ivSite)
    {
        if ($ivSite instanceof IvSite) {
            return $ivSite->toArray();
        }

        return $ivSite;
    }
}
