<?php namespace Fisdap\Attachments\Core\ConfigProvider;

use Illuminate\Contracts\Config\Repository;

/**
 * Wrapper for Laravel configuration repository for use with AttachmentsKernel
 *
 * @package Fisdap\Attachments\Core\ConfigProvider
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class LaravelConfigProvider implements ProvidesConfig
{
    /**
     * @var Repository
     */
    protected $config;


    /**
     * @param Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
    }


    /**
     * @inheritdoc
     */
    public function get($name, $default = null)
    {
        return $this->config->get(self::CONFIG_NAMESPACE . ".$name", $default);
    }


    /**
     * @inheritdoc
     */
    public function set($name, $value)
    {
        $this->config->set(self::CONFIG_NAMESPACE . ".$name", $value);
    }
}
