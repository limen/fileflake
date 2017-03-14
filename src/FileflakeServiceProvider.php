<?php
/*
 * This file is part of the Fileflake package.
 *
 * (c) LI Mengxiang <limengxiang876@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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