<?php
/**
 * @author Li Mengxiang
 * @email limengxiang876@gmail.com
 * @since 2016/6/8 10:52
 */

namespace Limen\Fileflake;

use Limen\Fileflake\Exceptions\FileException;
use Limen\Fileflake\Lockers\FileLocker;
use Limen\Fileflake\Lockers\LockerInterface;
use Limen\Fileflake\Protocols\InputFile;
use Limen\Fileflake\Protocols\OutputFile;
use Limen\Fileflake\Storage\FileContainer;
use Limen\Fileflake\Storage\Handlers\NodeMetaHandler;
use Limen\Fileflake\Storage\Handlers\StorageNodeHandler;
use Limen\Fileflake\Storage\Meta\FileMeta;
use Limen\Fileflake\Storage\StorageNodeSelector;
use Limen\Fileflake\Support\UidGenerator;
use Limen\Fileflake\Config;

class Fileflake
{
    /** @var LockerInterface */
    protected $locker;

    public function __construct(array $config, LockerInterface $locker = null)
    {
        Config::load($config);
        $this->locker = $locker ?: new FileLocker();
    }

    public function put($path, $name, $size, $extension, $mimeType)
    {

        /** @var InputFile $fileInfo */
        $fileInfo = new InputFile($path, $name, $size, $extension, $mimeType);

        $fid = UidGenerator::id($fileInfo->checksum);
        // lock the file while uploading
        if (!$this->locker->lock($fid)) {
            FileException::pop('Fail to lock file. Try later.');
        }

        $fileInfo->id($fid);

        $fileMeta = new FileMeta();

        $fileContainer = new FileContainer();

        $fileContainer->addHandler(new StorageNodeHandler());
        $fileContainer->addHandler(new NodeMetaHandler());

        $fileMeta->setFileContainer($fileContainer);

        if ($sourceFileInfo = $fileMeta->checkExist($fileInfo)) {
            // make the upload file a soft link
            // which refers to the source file
            // lock the source file first to prevent from being deleted
            if (! $this->locker->lock($sourceFileInfo->id)) {
                FileException::pop('Fail to lock file. Try later.');
            }
            $fileMeta->softLink($fileInfo, $sourceFileInfo);
            $this->locker->unlock($sourceFileInfo->id);
        } else {
            $fileInfo->nodeId = (new StorageNodeSelector())->getNodeId();
            $fileMeta->add($fileInfo);
        }

        $fileContainer->dump();

        $this->locker->unlock($fid);

        return $fileInfo->id;
    }

    /**
     * @param $id string
     * @param bool $localize
     * @return OutputFile|null
     */
    public function get($id, $localize = true)
    {
        $fileMeta = new FileMeta();
        $fileInfo = $fileMeta->get($id);

        if ($fileInfo === null) {
            FileException::pop("The file \"$id\" doesn't exist.");
        }

        if ($localize) {
            $fileInfo->localize();
        }

        return new OutputFile($fileInfo->name, $fileInfo->path, $fileInfo->size, $fileInfo->extension);
    }

    /**
     * Remove file
     * @param $id string file id
     * @return bool|null
     */
    public function remove($id)
    {
        // wait if the file is locked
        if (!$this->locker->lock($id)) {
            FileException::pop("The file \"$id\" is locked. Try later.");
        }

        $fileMeta = new FileMeta();

        $fileContainer = new FileContainer();

        $fileContainer->addHandler(new StorageNodeHandler());
        $fileContainer->addHandler(new NodeMetaHandler());

        $fileMeta->setFileContainer($fileContainer);

        $fileMeta->remove($id);

        $fileContainer->dump();

        $this->locker->unlock($id);

        return true;
    }
}