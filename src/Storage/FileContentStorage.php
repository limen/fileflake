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

use Limen\Fileflake\Protocols\FileProtocol;
use Limen\Fileflake\Storage\Models\FileStorageModel;

class FileContentStorage
{
    protected $id;

    protected $fileNode;

    public function __construct($id)
    {
        $this->id = $id;
        $this->fileNode = new FileStorageModel($id);
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $file FileProtocol
     * @return mixed
     */
    public function add($file)
    {
        foreach ($file->chunkIds as $chunkId) {
            $this->fileNode->add($chunkId, $file->getChunkContent($chunkId));
        }
    }

    /**
     * @param $file FileProtocol
     * @return mixed
     */
    public function remove($file)
    {
        return $this->fileNode->remove($file->chunkIds);
    }

    /**
     * @param $chunkId
     * @return null|string
     */
    public function getChunk($chunkId)
    {
        return $this->fileNode->get($chunkId);
    }

}