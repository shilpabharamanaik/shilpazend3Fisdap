<?php namespace Fisdap\Api\Shifts\PreceptorSignoffs\Transformation;


use Fisdap\Entity\PreceptorSignoff;
use Fisdap\Fractal\Transformer;

/**
 * Class PreceptorSignoffsTransformer
 * @package Fisdap\Api\Shifts\PreceptorSignoffs\Transformation
 * @author  Isaac White <isaac.white@ascendlearning.com>
 */
final class PreceptorSignoffsTransformer extends Transformer
{
    public function transform($signoff)
    {
        if ($signoff instanceof PreceptorSignoff) {
            $ratings = $signoff->getRatings();
            $patient = $signoff->getPatient();
            $shift = $signoff->getShift();
            //$verification = $signoff->getVerification();
            $signoff = $signoff->toArray();

            // For reasons unknown, there are duplicates in the ratings array. I'm removing the duplicates here
            // but we should probably figure out why they're being duplicated by doctrine. Note: the duplicates
            // are NOT in the database.
            // - Nick
            $signoff['ratings'] = array();
            foreach($ratings as $rating) {
                $found = false;
                foreach($signoff['ratings'] as $existingRating) {
                    if($rating->id == $existingRating['id']) {
                        $found = true;
                        break;
                    }
                }

                if(!$found) {
                    $signoff['ratings'][] = $rating->toArray();
                }
            }

            unset(
                $signoff['created'],
                $signoff['updated']
            );


            if (is_null($signoff['uuid'])) {
                $this->removeFields([
                    "uuid"
                ], $signoff);
            }

            if (isset($verification)) {
                $signoff['verification'] = $verification->toArray();
            } elseif ($patient) {
                if($patient->getVerification() != null && $patient->getVerification()->getSignature() != null) {
                    $signature = $patient->getVerification()->getSignature();

                    // This is only needed to return the signature string after creation.
                    // Because of the way pre and post persist works, the string being returned after creation is
                    // the compressed string. In order to figure out which situation we're in, I'm checking the encoding
                    // of the string.
                    if (!mb_detect_encoding($signature->getSignatureString(), 'UTF-8', true)) {
                        $signature->setSignatureString(
                            gzuncompress($signature->getSignatureString())
                        );
                    }
                }

                $signoff['verification'] = $patient->getVerificationArray() ? $patient->getVerificationArray() : null;
            } elseif ($shift) {
                if($shift->getVerification() != null && $shift->getVerification()->getSignature() != null) {
                    $signature = $shift->getVerification()->getSignature();

                    // This is only needed to return the signature string after creation.
                    // Because of the way pre and post persist works, the string being returned after creation is
                    // the compressed string. In order to figure out which situation we're in, I'm checking the encoding
                    // of the string.
                    if (!mb_detect_encoding($signature->getSignatureString(), 'UTF-8', true)) {
                        $signature->setSignatureString(
                            gzuncompress($signature->getSignatureString())
                        );
                    }
                }

                $signoff['verification'] = $shift->getVerificationArray() ? $shift->getVerificationArray() : null;
            }

            return $signoff;
        }

        return [];
    }
}
