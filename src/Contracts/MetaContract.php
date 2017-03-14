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

use Limen\Fileflake\Protocols\FileProtocol;

interface MetaContract
{
    /**
     * @param FileProtocol $file
     * @return mixed
     */
    public function add($file);

    /**
     * @param string $id
     * @return FileProtocol
     */
    public function get($id);

    /**
     * @param FileProtocol $file
     * @return mixed
     */
    public function remove($file);
}