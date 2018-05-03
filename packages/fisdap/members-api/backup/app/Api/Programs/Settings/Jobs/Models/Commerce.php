<?php namespace Fisdap\Api\Programs\Settings\Jobs\Models;

use Swagger\Annotations as SWG;

/**
 * Class Commerce
 *
 * @package Fisdap\Api\Programs\Settings\Jobs\Models
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @author  Nick Karnick <nkarnick@fisdap.net>
 *
 * @SWG\Definition(definition="ProgramSettingsCommerce")
 */
final class Commerce
{
    /**
     * @var int
     * @see ProgramLegacy::$order_permission
     * @SWG\Property(example=1)
     */
    public $orderPermissionId = 1;

    /**
     * @var bool
     * @see ProgramLegacy::$requires_po
     * @SWG\Property(example=false)
     */
    public $requiresPurchaseOrder = false;
}
