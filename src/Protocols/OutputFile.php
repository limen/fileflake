<?php
/*
 * This file is part of the Fileflake package.
 *
 * (c) LI Mengxiang <limengxiang876@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Limen\Fileflake\Protocols;

use Limen\Fileflake\Config;
use Limen\Fileflake\Exceptions\Exception;
use Limen\Fileflake\LoadBalancer;
use Limen\Fileflake\Storage\FileContentStorage;
use Limen\Fileflake\Support\FileUtil;

class OutputFile extends FileProtocol
{
    /** @var array */
    protected $iteratingChunkIds;

    /** @var FileContentStorage */
    protected $storageNode;

    /**
     * make local file
     * @return $this
     */
    public function localize()
    {
        $this->prepare();

        $file = Config::get(Config::KEY_LOCALIZE_DIR) . '/' . $this->id;

        if (file_exists($file)) {
            unlink($file);
        }

        foreach ($this->chunkIds as $chunkId) {
            $content = $this->storageNode->getChunk($chunkId);
            FileUtil::appendStreamToFile($content, $file);
        }

        $this->path = $file;

        return $this;
    }

    /**
     * Iterate file chunks
     * @return null|string
     */
    public function nextChunk()
    {
        $this->prepare();

        $chunkId = array_shift($this->iteratingChunkIds);

        if (!$chunkId) {
            return null;
        }

        return $this->storageNode->getChunk($chunkId);
    }

    /**
     * Delete local file
     */
    public function delete()
    {
        unlink($this->path);
    }

    protected function prepare()
    {
        if ($this->iteratingChunkIds === null) {
            $this->iteratingChunkIds = $this->chunkIds;
        }

        if ($this->storageNode === null) {
            $this->storageNode = LoadBalancer::getInstance()->get($this->nodeId);

            if (!$this->storageNode) {
                throw new Exception('Storage node not available');
            }
        }
    }
}