<?php

namespace Cartalyst\Sentinel\Lumen\Providers;

use Cartalyst\Sentinel\Laravel\SentinelServiceProvider as LaravelServiceProvider;
use Cartalyst\Sentinel\Lumen\Persistences\StatelessPersistenceRepository;

class SentinelServiceProvider extends LaravelServiceProvider
{
    /**
     * Prepare the package resources.
     *
     * @return void
     */
    protected function prepareResources()
    {
        $this->app->configure('cartalyst.sentinel');

        // Merge default config from sentinel package
        $reflection = new \ReflectionClass(LaravelServiceProvider::class);
        $dir = dirname($reflection->getFileName());
        $config = realpath($dir.'/../config/config.php');

        $this->mergeConfigFrom($config, 'cartalyst.sentinel');
    }

    /**
     * Registers the persistences.
     *
     * @return void
     */
    protected function registerPersistences()
    {
        /* Dropping session and cookie registration for Lumen 5.2+ (stateless) support. */

        $this->app->singleton('sentinel.persistence', function ($app) {
            $config = $app['config']->get('cartalyst.sentinel');

            $model  = array_get($config, 'persistences.model');
            $users  = array_get($config, 'users.model');

            if (class_exists($users) && method_exists($users, 'setPersistencesModel')) {
                forward_static_call_array([$users, 'setPersistencesModel'], [$model]);
            }

            return new StatelessPersistenceRepository();
        });
    }
}