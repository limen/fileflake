<?php
/**
 * @author Li Mengxiang
 * @email limengxiang876@gmail.com
 * @since 2016/6/7 9:45
 */

namespace Limen\Fileflake\Protocols;

use Limen\Fileflake\Config;
use Limen\Fileflake\Exceptions\FileException;
use Limen\Fileflake\Storage\Models\BaseModel;
use Limen\Fileflake\Storage\Models\FileMetaModel;
use Limen\Fileflake\Storage\StorageNodeSelector;
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
 * @property string mimeType    mime
 */
class InputFile
{
    protected $legalAttributes = [
        'id',
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
        'mimeType',
    ];

    protected $fileInfo = [];

    public function __construct($path = null, $name = null, $size = null, $extension = null, $mimeType = null)
    {
        $this->path = $path;
        $this->name = $name;
        $this->size = $size;
        $this->extension = $extension;
        $this->mimeType = $mimeType;
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
    public static function initWithFileMeta(BaseModel $fileInfo)
    {
        $instance = new static();

        foreach ($fileInfo->toArray() as $k => $v) {
            $instance->$k = $v;
        }

        return $instance;
    }

    public function id($value)
    {
        $this->id = $value;
        $this->setChunkIds();
        return $this;
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
        return FileUtil::fileToString($this->path, $offset, $length);
    }

    /**
     * make local file
     */
    public function localize()
    {
        $file = (new StorageNodeSelector())->getNodeById($this->nodeId)->localize($this);
        $this->path = $file;
    }

    public function __get($name)
    {
        return isset($this->fileInfo[$name]) ? $this->fileInfo[$name] : null;
    }

    public function __set($attr, $value)
    {
        if ($attr == '_id') {
            $attr = 'id';
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
            FileException::pop('File chunk size is not set');
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