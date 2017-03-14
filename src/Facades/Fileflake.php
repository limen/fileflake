<?php
/*
 * This file is part of the Fileflake package.
 *
 * (c) LI Mengxiang <limengxiang876@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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