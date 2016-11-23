<?php
namespace Limen\Fileflake\Storage\Models;

use Limen\Fileflake\Config;

/**
 * @author Li Mengxiang
 * @email limengxiang876@gmail.com
 * @since 2016/6/7 10:22
 */
class FileNodeModel
{
    protected $collection = 'FileHouse';

    /** @var  BaseModel */
    protected $model;

    public function __construct($nodeId)
    {
        $nodeConfig = Config::getFileNodeConfig($nodeId);
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
        $row = $this->model->ofId($chunkId);

        return $row ? $row['content'] : null;
    }
}