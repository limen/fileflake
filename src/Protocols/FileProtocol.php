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
 * @property string checksum    File checksum
 * @property int refCount    File's reference count
 * @property int deleted     File is deleted or not
 * @property int nodeId      File storage node id
 * @property int chunkSize   chunk size
 * @property array chunkIds    chunk ids
 * @property string mime    mime
 * @property string createdAt
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
        BaseModel::CREATED_AT,
        BaseModel::UPDATED_AT,
    ];

    protected $fileInfo = [];

    protected function __construct()
    {
        //
    }

    /**
     * @param string $path
     * @param string $name
     * @param int|null $size
     * @param string|null $extension
     * @param string|null $mime
     * @return static
     * @throws Exception
     */
    public static function make($path, $name = null, $size = null, $extension = null, $mime = null)
    {
        if (!file_exists($path)) {
            throw new Exception('File not exist: ' . $path);
        }

        $file = new static();

        $file->path = $path;
        $file->name = $name ?: basename($path);
        $file->size = $size ?: filesize($file->path);
        $file->extension = $extension;
        $file->mime = $mime;
        $file->refCount = 1;
        $file->deleted = 0;
        $file->reference = '';
        $file->checksum = FileUtil::checksum($file->path);
        $file->chunkSize = Config::get(Config::KEY_FILE_CHUNK_SIZE);

        return $file;
    }

    /**
     * @param BaseModel $fileInfo
     * @return static
     */
    public static function remake(BaseModel $fileInfo)
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

    public function incrRefCount($step = 1)
    {
        $this->refCount = $this->refCount + $step;

        return $this;
    }

    public function decrRefCount($step = 1)
    {
        $this->refCount = $this->refCount - $step;

        return $this;
    }

    public function toArray()
    {
        return $this->fileInfo;
    }

    public function toArrayForModel()
    {
        $data = $this->toArray();
        unset($data['path']);

        $now = time();
        if (!isset($data[BaseModel::CREATED_AT])) {
            $data[BaseModel::CREATED_AT] = $now;
        }
        if (!isset($data[BaseModel::UPDATED_AT])) {
            $data[BaseModel::UPDATED_AT] = $now;
        }

        return $data;
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
