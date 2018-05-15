<?php namespace Fisdap\Api\Http\Batching;

use Illuminate\Support\ServiceProvider;
use Route;

/**
 * Provides routes and REST API endpoint documentation for batch requests
 *
 * @package Fisdap\Api\Http\Batching
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
final class BatchRequestServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function boot()
    {
        Route::post('batch/get', ['uses' => BatchController::class . '@processGetRequests']);
        Route::post('batch/delete', ['uses' => BatchController::class . '@processDeleteRequests']);
    }

    /**
     * @inheritdoc
     */
    public function register()
    {
    }
}
