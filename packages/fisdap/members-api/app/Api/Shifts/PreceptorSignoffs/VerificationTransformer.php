<?php namespace Fisdap\Api\Shifts\PreceptorSignoffs;

use Fisdap\Entity\Verification;
use Fisdap\Fractal\Transformer;

/**
 * Prepares verification data for JSON output
 *
 * @package Fisdap\Api\Shifts\PreceptorSignoff
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class VerificationTransformer extends Transformer
{
    /**
     * @param Verification|array $verification
     *
     * @return array
     */
    public function transform($verification)
    {
        if ($verification instanceof Verification) {
            return $verification->toArray();
        }

        $transformedVerification = [
            'id'          => $verification['id'],
            'verified'    => $verification['verified'],
            'verified_by' => $verification['verified_by']
        ];

        $this->addCommonTimestamps($transformedVerification, $verification);

        return $transformedVerification;
    }
}
