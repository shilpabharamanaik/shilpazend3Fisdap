<?php namespace Fisdap\Api\Users\UserContexts\Permissions\Transformation;

use Fisdap\Entity\Permission;
use League\Fractal\TransformerAbstract;


/**
 * Prepares permission data for JSON output
 *
 * @package Fisdap\Api\Users\UserContexts\Permissions\Transformation
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class PermissionTransformer extends TransformerAbstract
{
    /**
     * @var array
     */
    protected $defaultIncludes = [
        'category'
    ];


    /**
     * @param array|Permission $permission
     *
     * @return array
     */
    public function transform($permission)
    {
        if ($permission instanceof Permission) {
            $permission = $permission->toArray();
        }

        return $permission;
    }


    /**
     * @param array|Permission $permission
     *
     * @return \League\Fractal\Resource\Item|void
     */
    public function includeCategory($permission)
    {
        if ($permission instanceof Permission) {
            $category = $permission->getCategory();
        } else {
            $category = isset($permission['category']) ? $permission['category'] : null;
        }

        if ($category === null) return;

        return $this->item($category, new PermissionCategoryTransformer);
    }
}