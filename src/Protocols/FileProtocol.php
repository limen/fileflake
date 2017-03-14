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
use Limen\Fileflake\Storage\Models\BaseModel;
use Limen\Fileflake\Support\FileUtil;

/**
 * Class InputFile
 * @package Fileflake\Protocols
 * @property string id           ID
 * @property string reference    soft linked file id
 * @property string path         local path
 * @property int size         size on byte
 * @property string name        File client name
 * @property string extension   File extension
 * @property string checksum    File ID
 * @property int refCount    File's reference count
 * @property int deleted     File is deleted or not
 * @property int nodeId      File storage node id
 * @property int chunkSize   chunk size
 * @property array chunkIds    chunk ids
 * @property string $mime    mime
 */
class FileProtocol
{
    protected $legalAttributes = [
        '_id',
        'path',
        'size',
        'name',
        'reference',
        'extension',
        'checksum',
        'refCount',
        'deleted',
        'nodeId',
        'chunkSize',
        'chunkIds',
        'mime',
    ];

    protected $fileInfo = [];

    public function __construct($path = null, $name = null, $size = null, $extension = null, $mime = null)
    {
        $this->path = $path;
        $this->name = $name;
        $this->size = $size;
        $this->extension = $extension;
        $this->mime = $mime;
        $this->refCount = 1;
        $this->deleted = 0;
        $this->reference = '';
        $this->checksum = $this->path ? FileUtil::fileChecksum($this->path) : '';
        $this->chunkSize = Config::get(Config::KEY_FILE_CHUNK_SIZE);
    }

    /**
     * @param BaseModel $fileInfo
     * @return static
     */
    public static function initByMeta(BaseModel $fileInfo)
    {
        $instance = new static();

        foreach ($fileInfo->toArray() as $k => $v) {
            $instance->$k = $v;
        }

        return $instance;
    }

    public function setId($value)
    {
        $this->id = $value;
        $this->setChunkIds();

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function toArray()
    {
        return $this->fileInfo;
    }

    /**
     * Get file chunk content
     * @param $chunkId string chunk Id
     * @return bool|string
     */
    public function getChunkContent($chunkId)
    {
        $length = Config::get(Config::KEY_FILE_CHUNK_SIZE);
        $offset = (int)substr($chunkId, -1) * $length;
        return FileUtil::getFileStream($this->path, $offset, $length);
    }

    public function __get($name)
    {
        if ($name == 'id') {
            $name = '_id';
        }

        return isset($this->fileInfo[$name]) ? $this->fileInfo[$name] : null;
    }

    public function __set($attr, $value)
    {
        if ($attr == 'id') {
            $attr = '_id';
        }
        if (in_array($attr, $this->legalAttributes)) {
            $this->fileInfo[$attr] = $value;
        }
    }

    private function getChunkSize()
    {
        if (!$this->chunkSize) {
            $this->chunkSize = Config::get(Config::KEY_FILE_CHUNK_SIZE);
        }
        if (!$this->chunkSize) {
            throw new Exception('Chunk size not set');
        }

        return $this->chunkSize;
    }

    /**
     * Set file's chunk Ids
     */
    private function setChunkIds()
    {
        $chunkNum = ceil($this->size / $this->getChunkSize());

        $chunkIds = [];

        for ($i = 0; $i < $chunkNum; $i++) {
            $chunkIds[] = $this->id . '_' . $i;
        }

        $this->chunkIds = $chunkIds;
    }
}
