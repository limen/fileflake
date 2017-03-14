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

class FileStorageModel
{
    /** @var  BaseModel */
    protected $model;

    public function __construct($nodeId)
    {
        $nodeConfig = Config::getFileContentStorageConfig($nodeId);
        $this->model = (new BaseModel())
            ->setCollection($nodeConfig['collection'])
            ->setConnection($nodeConfig['connection']);
    }

    /**
     * @param $chunkId string
     * @param $content string
     * @return mixed
     */
    public function add($chunkId, $content)
    {
        return $this->model->insert([
            '_id'     => $chunkId,
            'content' => $content,
        ]);
    }

    /**
     * Remove a chunk
     * @param $chunkId string chunk Id
     * @return mixed
     */
    public function remove($chunkId)
    {
        return $this->model->deleteById($chunkId);
    }

    /**
     * Get chunk content
     * @param $chunkId string chunk Id
     * @return string|null
     */
    public function get($chunkId)
    {
        $row = $this->model->findById($chunkId);

        return $row ? $row['content'] : null;
    }
}