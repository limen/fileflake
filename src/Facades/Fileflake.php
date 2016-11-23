<?php
/**
 * @author Li Mengxiang
 * @email limengxiang876@gmail.com
 * @since 2016/6/8 10:56
 */

namespace Limen\Fileflake\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Fileflake facade
 * Class Fileflake
 * @package Fileflake\Facades
 */
class Fileflake extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'fileflake';
    }
}