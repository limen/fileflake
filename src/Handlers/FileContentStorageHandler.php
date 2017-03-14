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
use Limen\Fileflake\LoadBalancer;

/**
 * Handle involved files and decide to update storage node or not.
 * Class FileContentStorageHandler
 * @package Fileflake\Storage\Nodes
 */
class FileContentStorageHandler implements HandlerContract
{
    public function handleAdded($file)
    {
        if ($file->nodeId && !$file->reference) {
            $storageNode = LoadBalancer::getInstance()->get($file->nodeId);
            $storageNode->add($file);
        }
    }

    public function handleRemoved($file)
    {
        if ($file->refCount == 0 && !$file->reference) {
            $storageNode = LoadBalancer::getInstance()->get($file->nodeId);
            $storageNode->remove($file);
        }
    }

    public function handleTouched($file)
    {
        //
    }
}