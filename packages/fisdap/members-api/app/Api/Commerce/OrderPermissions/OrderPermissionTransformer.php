<?php namespace Fisdap\Api\Commerce\OrderPermissions;

use Fisdap\Entity\OrderPermission;
use Fisdap\Fractal\Transformer;


/**
 * Prepares order permission data for JSON output
 *
 * @package Fisdap\Api\Commerce\OrderPermissions
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class OrderPermissionTransformer extends Transformer
{
    /**
     * @param OrderPermission|array $orderPermission
     *
     * @return array
     */
    public function transform($orderPermission)
    {
        if ($orderPermission instanceof OrderPermission) {
            $orderPermission = $orderPermission->toArray();
        }

        return $orderPermission;
    }
} 