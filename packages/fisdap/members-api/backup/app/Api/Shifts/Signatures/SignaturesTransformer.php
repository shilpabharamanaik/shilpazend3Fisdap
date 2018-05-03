<?php namespace Fisdap\Api\Shifts\Signatures;


use Fisdap\Entity\Signature;
use Fisdap\Fractal\Transformer;

final class SignaturesTransformer extends Transformer
{
    /**
     * @param $patient
     *
     * @return array
     */
    public function transform($signature)
    {
        if ($signature instanceof Signature) {
            $signature = $signature->toArray();
            unset($signature['created']);
            unset($signature['updated']);
        }

        return $signature;
    }
}

