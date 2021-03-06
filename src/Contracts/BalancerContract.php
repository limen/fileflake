<?php
/*
 * This file is part of the Fileflake package.
 *
 * (c) LI Mengxiang <limengxiang876@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Limen\Fileflake\Contracts;

use Limen\Fileflake\Storage\FileContentStorage;

interface BalancerContract
{
    /**
     * @return FileContentStorage
     */
    public function select();

    /**
     * @param $id
     * @return FileContentStorage
     */
    public function get($id);
}