<?php namespace Fisdap\Attachments\Core\ConfigProvider;

/**
 * Contract for providing configuration capabilities
 *
 * @package Fisdap\Attachments\Core\ConfigProvider
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface ProvidesConfig
{
    const CONFIG_NAMESPACE = 'attachments';


    /**
     * Retrieve a configuration value.
     *
     * @param string $name
     * @param null   $default
     *
     * @return mixed
     */
    public function get($name, $default = null);


    /**
     * Set a configuration value.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function set($name, $value);
}
