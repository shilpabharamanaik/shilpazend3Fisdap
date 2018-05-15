<?php
/**
 * Created by PhpStorm.
 * User: jmortenson
 * Date: 3/4/15
 * Time: 3:48 PM
 */

namespace Fisdap\Doctrine\Extensions;

use Illuminate\Support\ServiceProvider;

/**
 * Class ServiceProvider
 * A Laravel/Illuminate service provider that enables all of the Fisdap doctrine extensions
 *
 * @package Fisdap\Doctrine\Extensions
 */
class FisdapDoctrineExtensionsServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Make the UUID column type available
        Bootstrap\UuidType::bootstrap();
    }
}
