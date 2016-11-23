<?php
/**
 * @author Li Mengxiang
 * @email limengxiang876@gmail.com
 * @since 2016/6/7 9:59
 */

namespace Limen\Fileflake\Storage;

use Limen\Fileflake\Config;
use Limen\Fileflake\Protocols\InputFile;
use Limen\Fileflake\Storage\Models\FileNodeModel;
use Limen\Fileflake\Support\FileUtil;

class StorageNode
{
    protected $fileNode;

    public function __construct($id)
    {
        $this->fileNode = new FileNodeModel($id);
    }

    /**
     * @param $file InputFile
     * @return mixed
     */
    public function add($file)
    {
        foreach ($file->chunkIds as $chunkId) {
            $this->fileNode->add($chunkId, $file->getChunkContent($chunkId));
        }
    }

    /**
     * @param $file InputFile
     * @return mixed
     */
    public function remove($file)
    {
        return $this->fileNode->remove($file->chunkIds);
    }

    /**
     * make local file
     * @param $fileInfo InputFile
     * @return string
     */
    public function localize($fileInfo)
    {
        $file = Config::get(Config::KEY_LOCALIZE_DIR) . '/' . $fileInfo->id;

        if (file_exists($file)) {
            unlink($file);
        }

        foreach ($fileInfo->chunkIds as $chunkId) {
            $content = $this->fileNode->get($chunkId);
            FileUtil::appendStringToFile($content, $file);
        }
        return $file;
    }
}