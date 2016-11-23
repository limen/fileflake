<?php
/**
 * @author Li Mengxiang
 * @email limengxiang876@gmail.com
 * @since 2016/6/7 9:51
 */

namespace Limen\Fileflake\Storage\Handlers;

use Limen\Fileflake\Protocols\InputFile;

/**
 * File handler interface.
 * The subclass should handle the involved files properly.
 * Interface FileHandlerInterface
 * @package Fileflake\Storage
 */
interface FileHandlerInterface
{
    /**
     * Handle added file
     * @param $file InputFile
     * @return mixed
     */
    public function handleAdded($file);

    /**
     * Handle removed file
     * @param $file InputFile
     * @return mixed
     */
    public function handleRemoved($file);
}