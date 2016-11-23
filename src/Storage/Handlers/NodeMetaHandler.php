<?php
/**
 * @author Li Mengxiang
 * @email limengxiang876@gmail.com
 * @since 2016/6/7 9:53
 */

namespace Limen\Fileflake\Storage\Handlers;

use Limen\Fileflake\Storage\Meta\NodeMeta;

/**
 * Handle the involved files inside FileContainer and decide to update node meta or not.
 * Class NodeMetaHandler
 * @package Fileflake\Storage\Meta
 */
class NodeMetaHandler implements FileHandlerInterface
{
    public function handleAdded($file)
    {
        if ($file->nodeId && !$file->reference) {
            $nodeMeta = new NodeMeta();
            $nodeMeta->add($file);
        }
    }

    public function handleRemoved($file)
    {
        if ($file->nodeId && !$file->reference && $file->refCount == 0) {
            $nodeMeta = new NodeMeta();
            $nodeMeta->remove($file);
        }
    }
}