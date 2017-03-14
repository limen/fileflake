<?php
/*
 * This file is part of the Fileflake package.
 *
 * (c) LI Mengxiang <limengxiang876@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Limen\Fileflake\Handlers;

use Limen\Fileflake\Contracts\HandlerContract;
use Limen\Fileflake\Storage\NodeMetaStorage;

/**
 * Handle the involved files inside FileContainer and decide to update node meta or not.
 * Class NodeMetaHandler
 * @package Fileflake\Storage\Meta
 */
class NodeMetaHandler implements HandlerContract
{
    public function handleAdded($file)
    {
        if ($file->nodeId && !$file->reference) {
            $nodeMeta = new NodeMetaStorage();
            $nodeMeta->add($file);
        }
    }

    public function handleRemoved($file)
    {
        if ($file->nodeId && !$file->reference && $file->refCount == 0) {
            $nodeMeta = new NodeMetaStorage();
            $nodeMeta->remove($file);
        }
    }

    public function handleTouched($file)
    {
        //
    }
}