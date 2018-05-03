<?php namespace Fisdap\Attachments\Core\ConfigProvider;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\ServiceProvider;
use Monolog\Logger;

/**
 * Service provider for Laravel-based attachments configuration
 *
 * @package Fisdap\Attachments\Core\ConfigProvider
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class AttachmentsConfigServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->app->singleton(ProvidesConfig::class, function () {

            /** @var Repository $laravelConfig */
            $laravelConfig = $this->app['config'];

            $config = new LaravelConfigProvider($laravelConfig);

            if (! $config->get('app_url')) {
                $config->set('app_url', $laravelConfig->get('app.url'));
            }

            if (! $config->get('public_path')) {
                $config->set('public_path', realpath(public_path()));
            }

            if (! $config->get('temp_public_relative_path')) {
                $config->set('temp_public_relative_path', 'attachments' . DIRECTORY_SEPARATOR . 'temp');
            }

            if (! $config->get('base_path')) {
                $config->set('base_path', realpath(base_path()));
            }

            if (! $config->get('log_file')) {
                $config->set('log_file', storage_path('logs/attachments.log'));
            }

            if (! $config->get('log_level')) {
                $config->set('log_level', $laravelConfig->get('app.debug') == true ? Logger::DEBUG : Logger::INFO);
            }

            if (! $config->get('annotation_cache_path')) {
                $config->set('annotation_cache_path', storage_path('framework/cache/attachments'));
            }

            if (! $config->get('debug')) {
                $config->set('debug', $laravelConfig->get('app.debug'));
            }

            return $config;
        });
    }
}
