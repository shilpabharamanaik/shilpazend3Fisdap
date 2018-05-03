<?php namespace Fisdap\Api\Products\SerialNumbers;

use Fisdap\Entity\SerialNumberLegacy;
use Fisdap\Fractal\Transformer;

/**
 * Prepares serial number data for JSON output
 *
 * @package Fisdap\Api\Products\SerialNumbers
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class SerialNumberTransformer extends Transformer
{
    /**
     * @param mixed $serialNumber
     *
     * @return array
     */
    public function transform($serialNumber)
    {
        if ($serialNumber instanceof SerialNumberLegacy) {
            $serialNumber = $serialNumber->toArray();
        }

        $transformed = [
            "uuid"              => $serialNumber['uuid'],
            "number"            => $serialNumber['number'],
            "distMethod"        => $serialNumber['dist_method'],
            "accountType"       => $serialNumber['account_type'],
            "configuration"     => $serialNumber['configuration']
        ];

        if (is_null($transformed['uuid'])) {
            $this->removeFields([
                "uuid"
            ], $transformed);
        }

        return $transformed;
    }
}
