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

use Limen\Fileflake\Contracts\FileMetaContract;
use Limen\Fileflake\FileContainer;
use Limen\Fileflake\Protocols\FileProtocol;
use Limen\Fileflake\Protocols\InputFile;
use Limen\Fileflake\Protocols\OutputFile;
use Limen\Fileflake\Storage\Models\FileMetaModel;

class FileMetaStorage implements FileMetaContract
{
    /** @var  FileMetaModel */
    protected $fileMetaModel;

    public function __construct()
    {
        $this->fileMetaModel = new FileMetaModel();
    }

    /**
     * add file meta
     * @param InputFile $fileInfo
     * @return mixed
     */
    public function add($fileInfo)
    {
        if ($this->fileMetaModel->add($fileInfo)) {
            FileContainer::getInstance()->add($fileInfo);
        }

        return true;
    }

    /**
     * @param $fid
     * @return OutputFile|null
     */
    public function get($fid)
    {
        /** @var OutputFile $fileInfo */
        $fileInfo = $this->fileMetaModel->getById($fid);

        if ($fileInfo && $fileInfo->reference) {
            /** @var OutputFile $sourceFileInfo */
            $sourceFileInfo = $this->fileMetaModel->getSourceById($fileInfo->reference);
            $fileInfo->chunkIds = $sourceFileInfo->chunkIds;
        }

        return $fileInfo;
    }

    /**
     * @param FileProtocol $file
     * @return InputFile
     */
    public function remove($file)
    {
        $involvedFiles = [];

        if ($file->reference) {
            // soft link should be deleted immediately
            $this->fileMetaModel->remove($file->id);
            $file->refCount--;

            $involvedFiles[] = $file;

            // decrease the source file reference count by 1
            $involvedFiles[] = $this->fileMetaModel->decrFileRefCountById($file->reference);
        } else {
            // Source file is deleted and some other files still refer to it, do nothing
            if ($file->deleted && $file->refCount > 0) {
                return false;
            }
            // soft delete
            if ($file->refCount > 1) {
                $file = $this->fileMetaModel->softRemove($file);
            } else {
                $this->fileMetaModel->remove($file->id);
                // make sure the storage node would remove the file
                $file->refCount = 0;
            }
            $involvedFiles[] = $file;
        }

        foreach ($involvedFiles as $file) {
            FileContainer::getInstance()->remove($file);
        }
    }

    /**
     * Check if there is a same file.
     * @param InputFile $file
     * @return FileProtocol|null
     */
    public function getSource($file)
    {
        $row = $this->fileMetaModel->getByChecksum($file->checksum);

        return $row;
    }

    /**
     * make the file a "soft link" to the source file
     * @param $link FileProtocol
     * @param $source FileProtocol
     * @return mixed|void
     */
    public function makeSoftLink($link, $source)
    {
        // clean chunk Ids
        $link->chunkIds = [];
        $this->fileMetaModel->setFileReference($link, $source);
        $this->fileMetaModel->increaseRefCount($source);
    }

}