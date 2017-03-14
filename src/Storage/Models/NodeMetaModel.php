<?php
/*
 * This file is part of the Fileflake package.
 *
 * (c) LI Mengxiang <limengxiang876@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Limen\Fileflake\Storage\Models;

use Limen\Fileflake\Config;
use Limen\Fileflake\Protocols\FileProtocol;

class NodeMetaModel
{
    /** @var  BaseModel */
    protected $model;

    public function __construct()
    {
        $this->model = (new BaseModel())
            ->setConnection(Config::get(Config::KEY_NODE_META_CONNECTION))
            ->setCollection(Config::get(Config::KEY_NODE_META_COLLECTION));
    }

    /**
     * Update node meta after uploading file
     * @param FileProtocol $fileInfo
     * @return mixed
     */
    public function add($fileInfo)
    {
        $node = $this->model->findById($fileInfo->nodeId);
        if ($node) {
            return $this->model->updateOne($fileInfo->nodeId, [
                'fileCount' => $node->fileCount + 1,
                'volume'    => $node->volume + $fileInfo->size / 1024,
            ]);
        }
        return $this->model->insert([
            '_id'       => $fileInfo->nodeId,
            'fileCount' => 1,
            'volume'    => $fileInfo->size / 1024,
        ]);
    }

    /**
     * Update node meta after removing file
     * @param FileProtocol $fileInfo
     * @return bool
     */
    public function remove($fileInfo)
    {
        /** @var NodeMeta $node */
        $node = $this->model->findById($fileInfo->nodeId);
        if ($node) {
            return $this->model->updateOne($node->id, [
                'fileCount' => $node->fileCount - 1,
                'volume'    => $node->volume - $fileInfo->size,
            ]);
        }
        return false;
    }

    /**
     * Get node meta
     * @param $nodeId int Node Id
     * @return mixed
     */
    public function get($nodeId)
    {
        return $this->model->findById($nodeId);
    }

    /**
     * @return array
     */
    public function getUsedNodes()
    {
        return $this->model->where('fileCount', '>', 0)->get()->toArray();
    }
}