<?php namespace Fisdap\Api\Users\UserContexts\Permissions\Transformation;

use Fisdap\Entity\PermissionCategory;
use League\Fractal\TransformerAbstract;

/**
 * Prepares permission category data for JSON output
 *
 * @package Fisdap\Api\Users\UserContexts\Permissions\Transformation
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class PermissionCategoryTransformer extends TransformerAbstract
{
    /**
     * @param mixed $permissionCategory
     *
     * @return array
     */
    public function transform($permissionCategory)
    {
        if ($permissionCategory instanceof PermissionCategory) {
            $permissionCategory = $permissionCategory->toArray();
        }

        return $permissionCategory;
    }
}
