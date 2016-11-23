<?php
/**
 * @author Li Mengxiang
 * @email limengxiang876@gmail.com
 * @since 2016/6/7 9:49
 */

namespace Limen\Fileflake\Storage\Meta;

use Limen\Fileflake\Protocols\InputFile;
use Limen\Fileflake\Storage\FileContainer;
use Limen\Fileflake\Storage\Models\FileMetaModel;

class FileMeta
{
    const ACTION_REMOVE = 1;
    const ACTION_ADD = 0;
    /** @var FileContainer */
    protected $fileContainer;

    /** @var  FileMetaModel */
    protected $fileMetaModel;

    public function __construct()
    {
        $this->fileMetaModel = new FileMetaModel();
    }

    /**
     * @param $container FileContainer
     */
    public function setFileContainer($container)
    {
        $this->fileContainer = $container;
    }

    /**
     * add file meta
     * @param InputFile $fileInfo
     * @return mixed
     */
    public function add($fileInfo)
    {
        $this->fileMetaModel->add($fileInfo);
        $this->fileContainer && $this->fileContainer->add($fileInfo);
    }

    /**
     * @param $fid
     * @return InputFile|null
     */
    public function get($fid)
    {
        /** @var InputFile $fileInfo */
        $fileInfo = $this->fileMetaModel->getById($fid, true);

        if ($fileInfo && $fileInfo->reference) {
            /** @var InputFile $sourceFileInfo */
            $sourceFileInfo = $this->fileMetaModel->getById($fileInfo->reference);
            $fileInfo->chunkIds = $sourceFileInfo->chunkIds;
        }
        return $fileInfo;
    }

    /**
     * @param string $fid
     * @return InputFile
     */
    public function remove($fid)
    {
        $fileInfo = $this->get($fid);

        $involvedFiles = [];

        if ($fileInfo->reference) {
            // soft link should be deleted immediately
            $this->fileMetaModel->remove($fileInfo->id);
            $fileInfo->refCount--;

            $involvedFiles[] = $fileInfo;

            // decrease the source file reference count by 1
            $involvedFiles[] = $this->fileMetaModel->decrFileRefCountById($fileInfo->reference);

        } else {
            // Source file is deleted and some other files refer to it
            // do nothing
            if ($fileInfo->deleted && $fileInfo->refCount > 0) {
                return;
            }
            // soft delete
            if ($fileInfo->refCount > 1) {
                $fileInfo = $this->fileMetaModel->softRemove($fileInfo);
            } else {
                $this->fileMetaModel->remove($fileInfo->id);
                // make sure the storage node would remove the file
                $fileInfo->refCount = 0;
            }
            $involvedFiles[] = $fileInfo;
        }
        if ($this->fileContainer) {
            foreach ($involvedFiles as $file) {
                $this->fileContainer->remove($file);
            }
        }
    }

    /**
     * Check if there is a same file.
     * @param $fileInfo InputFile
     * @return InputFile
     */
    public function checkExist($fileInfo)
    {
        $row = $this->fileMetaModel->getByChecksum($fileInfo->checksum);
        return $row;
    }

    /**
     * make the file a "soft link" to the source file
     * @param $targetFile InputFile
     * @param $sourceFile InputFile
     */
    public function softLink($targetFile, $sourceFile)
    {
        // clean chunk Ids
        $targetFile->chunkIds = [];
        $this->fileMetaModel->setFileReference($targetFile, $sourceFile);
        $this->fileMetaModel->incrFileRefCount($sourceFile);
    }

}