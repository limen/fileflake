<?php
/**
 * @author Li Mengxiang
 * @email limengxiang876@gmail.com
 * @since 2016/6/7 13:09
 */

namespace Limen\Fileflake\Storage\Handlers;

use Limen\Fileflake\Storage\StorageNodeSelector;

/**
 * Handle involved files and decide to update storage node or not.
 * Class StorageNodeHandler
 * @package Fileflake\Storage\Nodes
 */
class StorageNodeHandler implements FileHandlerInterface
{
    public function handleAdded($file)
    {
        if ($file->nodeId && !$file->reference) {
            $storageNode = (new StorageNodeSelector())->getNodeById($file->nodeId);
            $storageNode->add($file);
        }
    }

    public function handleRemoved($file)
    {
        if ($file->refCount == 0 && !$file->reference) {
            $storageNode = (new StorageNodeSelector())->getNodeById($file->nodeId);
            $storageNode->remove($file);
        }
    }
}