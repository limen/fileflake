<?php
/**
 * @author Li Mengxiang
 * @email limengxiang876@gmail.com
 * @since 2016/6/7 10:21
 */
namespace Limen\Fileflake\Storage\Models;

use Limen\Fileflake\Config;
use Limen\Fileflake\Protocols\InputFile;
use Limen\Fileflake\Traits\ModelTrait;

/**
 * Class FileMetaModel
 * @package Limen\Fileflake\Storage\Models
 * @property string id           ID
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
 */
class FileMetaModel
{
    use ModelTrait;

    /** @var BaseModel */
    protected $model;

    protected $columns = [
        '_id',
        'name',
        'nodeId',
        'checksum',
        'reference',
        'refCount',
        'size',
        'extension',
        'deleted',
        'chunkSize',
        'chunkIds',
        'mimeType',
    ];

    public function __construct()
    {
        $this->model = (new BaseModel())
            ->setConnection(Config::get(Config::KEY_FILE_META_CONNECTION))
            ->setCollection(Config::get(Config::KEY_FILE_META_COLLECTION));
    }

    /**
     * @param $fileInfo InputFile
     * @return mixed
     */
    public function add($fileInfo)
    {
        if ($fileInfo) {
            $fileInfo = $fileInfo->toArray();
            $fileInfo[$this->model->getKeyName()] = $fileInfo['id'];
            return $this->model->insert($this->filterAttributes($fileInfo, $this->columns));
        }

        return false;
    }

    public function remove($fid)
    {
        return $this->model->deleteById($fid);
    }

    /**
     * @param $fileInfo InputFile
     * @return InputFile
     */
    public function softRemove($fileInfo)
    {
        $fileInfo->refCount--;
        $fileInfo->deleted = 1;
        $this->model->updateOne($fileInfo->id, $this->filterAttributes($fileInfo->toArray(), $this->columns));
        return $fileInfo;
    }

    /**
     * @param $fid
     * @param bool $nonDeleted
     * @return null|InputFile
     */
    public function getById($fid, $nonDeleted = false)
    {
        /** @var static $row */
        $row = $this->model->ofId($fid);

        return $row && !(bool)$row->deleted === $nonDeleted ? InputFile::initWithFileMeta($row) : null;
    }

    /**
     * @param $checksum
     * @return null|InputFile
     */
    public function getByChecksum($checksum)
    {
        /** @var static $row */
        $row = $this->model->ofChecksum($checksum);

        return $row ? InputFile::initWithFileMeta($row) : null;
    }

    /**
     * @param $file InputFile
     * @param $refTo InputFile
     */
    public function setFileReference($file, $refTo)
    {
        $file = $file->toArray();
        $file[$this->model->getKeyName()] = $file['id'];
        $file['reference'] = $refTo->id;
        $file['nodeId'] = $refTo->nodeId;
        // unset checksum
        unset($file['checksum']);
        return $this->model->insert($this->filterAttributes($file, $this->columns));
    }

    /**
     * @param $file InputFile
     * @return bool
     */
    public function incrFileRefCount($file)
    {
        /** @var static $row */
        $row = $this->model->ofId($file->id);
        if ($row) {
            return $this->model->updateOne($file->id, [
                'refCount' => $row->refCount + 1,
            ]);
        }
        return false;
    }

    /**
     * @param $file InputFile
     * @return InputFile
     */
    public function decrFileRefCount($file)
    {
        /** @var static $row */
        $row = $this->model->ofId($file->id);
        $row->refCount--;

        $fileInfo = InputFile::initWithFileMeta($row);

        if ($row->refCount == 0) {
            $this->model->deleteById($row->id);
        } else {
            $this->model->updateOne($row->id, [
                'refCount' => $row->refCount,
            ]);
        }
        return $fileInfo;
    }

    /**
     * @param $fid
     * @return InputFile
     */
    public function decrFileRefCountById($fid)
    {
        /** @var static $row */
        $row = $this->model->ofId($fid);
        $row->refCount--;

        $fileInfo = InputFile::initWithFileMeta($row);

        if ($row->refCount == 0) {
            $this->model->deleteById($row->id);
        } else {
            $this->model->updateOne($row->id, [
                'refCount' => $row->refCount,
            ]);
        }

        return $fileInfo;
    }
}