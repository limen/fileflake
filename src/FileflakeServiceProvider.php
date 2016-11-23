<?php
/**
 * @author Li Mengxiang
 * @email limengxiang876@gmail.com
 * @since 2016/6/8 10:48
 */

namespace Limen\Fileflake;

use Illuminate\Support\ServiceProvider;

class FileflakeServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $app = $this->app;

        // create fileflake
        $app['fileflake'] = $app->share(function ($app) {
            return new Fileflake();
        });

        $app->alias('fileflake', 'Fileflake\Fileflake');
    }
}