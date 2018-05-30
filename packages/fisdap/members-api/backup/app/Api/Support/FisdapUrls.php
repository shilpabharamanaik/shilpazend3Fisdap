<?php namespace Fisdap\Api\Support;

use App;

/**
 * Class FisdapUrls
 *
 * @package Fisdap\Api\Support
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class FisdapUrls
{
    /**
     * @var array
     */
    public static $domains = [
        'local'       => 'local.fisdapdev.net',
        'dev'         => 'fisdapdev.net',
        'development' => 'fisdapdev.net',
        'qa'          => 'fisdapqa.net',
        'stage'       => 'fisdapstage.net',
        'staging'     => 'fisdapstage.net',
        'prod'        => 'fisdap.net',
        'production'  => 'fisdap.net',
        'testing'     => 'local.fisdapdev.net',
    ];


    /**
     * @return mixed
     */
    public static function getDomainName()
    {
        if (class_exists(App::class)) {
            return self::$domains[App::environment()];
        } elseif (class_exists(\Zend_Registry::class)) {
            return self::$domains[\Zend_Registry::get('container')->environment()];
        } else {
            return null;
        }
    }


    /**
     * @return string
     */
    public static function getWwwUrl()
    {
        return 'https://www.' . self::getDomainName();
    }


    /**
     * @return string
     */
    public static function getMembersUrl()
    {
        return 'https://members.' . self::getDomainName();
    }


    /**
     * @return string
     */
    public static function getMembersLegacyUrl()
    {
        return 'https://members1.' . self::getDomainName();
    }


    /**
     * @return string
     */
    public static function getTestingUrl()
    {
        return 'https://testing.' . self::getDomainName();
    }
}
