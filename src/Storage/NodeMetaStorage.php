<?php
/*
 * This file is part of the Fileflake package.
 *
 * (c) LI Mengxiang <limengxiang876@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Limen\Fileflake\Storage;

use Limen\Fileflake\Contracts\MetaContract;
use Limen\Fileflake\Protocols\FileProtocol;
use Limen\Fileflake\Storage\Models\NodeMetaModel;

class NodeMetaStorage implements MetaContract
{
    public function get($id)
    {
        //
    }

    /**
     * @param $file FileProtocol
     * @return mixed
     */
    public function add($file)
    {
        return (new NodeMetaModel())->add($file);
    }

    /**
     * @param $file FileProtocol
     * @return mixed
     */
    public function remove($file)
    {
        return (new NodeMetaModel())->remove($file);
    }

}