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
use Limen\Fileflake\Protocols\InputFile;
use Limen\Fileflake\Protocols\OutputFile;

/**
 * Class FileMetaModel
 * @package Limen\Fileflake\Storage\Models
 * @property string id           ID
 * @property string path         local path
 * @property int size         size on byte
 * @property string name        File client name
 * @property string extension   File extension
 * @property string checksum    File ID
 * @property string reference   Source file id
 * @property int refCount    File's reference count
 * @property int deleted     File is deleted or not
 * @property int nodeId      File storage node id
 * @property int chunkSize   chunk size
 * @property array chunkIds    chunk ids
 */
class FileMetaModel
{
    /** @var BaseModel */
    protected $model;

    public function __construct()
    {
        $this->init();
    }

    protected function init()
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
            $data = $fileInfo->toArrayForModel();
            return $this->model->insert($data);
        }

        return false;
    }

    /**
     * @param $fid
     * @return mixed
     */
    public function remove($fid)
    {
        return $this->model->deleteById($fid);
    }

    /**
     * @param $fileInfo FileProtocol
     * @return FileProtocol
     */
    public function softRemove($fileInfo)
    {
        $fileInfo->decrRefCount();
        $fileInfo->deleted = 1;
        $this->model->updateOne($fileInfo->getId(), $fileInfo->toArray());
        return $fileInfo;
    }

    /**
     * Get file by id, should not deleted if source file
     * @param $fid
     * @return null|OutputFile
     */
    public function getById($fid)
    {
        /** @var static $row */
        $row = $this->model->findById($fid);

        return $row && $row->deleted === 0 ? OutputFile::remake($row) : null;
    }

    /**
     * Get source file by id
     * @param $fid
     * @return null|OutputFile
     */
    public function getSourceById($fid)
    {
        /** @var static $row */
        $row = $this->model->findById($fid);

        return $row && empty($row->reference) ? OutputFile::remake($row) : null;
    }

    /**
     * @param $checksum
     * @return null|InputFile
     */
    public function getByChecksum($checksum)
    {
        /** @var static $row */
        $row = $this->model->findByChecksum($checksum);

        return $row ? InputFile::remake($row) : null;
    }

    /**
     * @param $link FileProtocol
     * @param $source FileProtocol
     */
    public function setFileReference($link, $source)
    {
        $link->reference = $source->getId();
        $link->nodeId = $source->nodeId;
        $link->chunkIds = $source->chunkIds;
        $link->checksum = '';

        $data = $link->toArrayForModel();

        return $this->model->insert($data);
    }

    /**
     * @param $file FileProtocol
     * @return bool
     */
    public function increaseRefCount($file)
    {
        return $this->model->increaseRefCount($file->getId());
    }

    /**
     * @param $file InputFile
     * @return InputFile
     */
    public function decrFileRefCount($file)
    {
        /** @var static $row */
        $row = $this->model->findById($file->id);
        $row->refCount = $row->refCount - 1;

        $fileInfo = InputFile::remake($row);

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
     * @return FileProtocol
     */
    public function decrRefCountById($fid)
    {
        /** @var static $row */
        $row = $this->model->findById($fid);
        $row->refCount = $row->refCount - 1;

        $fileInfo = FileProtocol::remake($row);

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