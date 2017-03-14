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
use Limen\Fileflake\Storage\FileContentStorage;
use Limen\Fileflake\Support\FileUtil;

class OutputFile extends FileProtocol
{
    /**
     * make local file
     * @param FileContentStorage $storageNode
     * @return $this
     */
    public function localize($storageNode)
    {
        $file = Config::get(Config::KEY_LOCALIZE_DIR) . '/' . $this->id;

        if (file_exists($file)) {
            unlink($file);
        }

        foreach ($this->chunkIds as $chunkId) {
            $content = $storageNode->getChunk($chunkId);
            FileUtil::appendStreamToFile($content, $file);
        }

        $this->path = $file;

        return $this;
    }

    /**
     * Delete local file
     */
    public function delete()
    {
        unlink($this->path);
    }
}