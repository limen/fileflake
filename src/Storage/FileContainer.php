<?php
/**
 * @author Li Mengxiang
 * @email limengxiang876@gmail.com
 * @since 2016/6/7 10:03
 */

namespace Limen\Fileflake\Storage;

use Limen\Fileflake\Protocols\InputFile;
use Limen\Fileflake\Storage\Handlers\FileHandlerInterface;

/**
 * One request may involved more than one file.
 * The container would store the involved files temporarily.
 * The container would invoke file handlers and flush the involved files at the end.
 * Class FileContainer
 * @package Fileflake\Storage\Containers
 */
class FileContainer
{
    /** @var InputFile[] */
    protected $added = [];
    /** @var InputFile[] */
    protected $removed = [];
    /** @var FileHandlerInterface[] */
    protected $handlers = [];

    /**
     * "add" file
     * @param $file InputFile
     */
    public function add($file)
    {
        $this->added[$file->id] = $file;
    }

    /**
     * "remove" file
     * @param $file InputFile
     */
    public function remove($file)
    {
        $this->removed[$file->id] = $file;
    }

    /**
     * "update" file
     * @param $file InputFile
     */
    public function update($file)
    {
        $this->updated[$file->id] = $file;
    }

    /**
     * Invoke the consumers and flush the files
     */
    public function dump()
    {
        foreach ($this->handlers as $handler) {
            if ($this->removed) {
                foreach ($this->removed as $file) {
                    $handler->handleRemoved($file);
                }
            }
            if ($this->added) {
                foreach ($this->added as $file) {
                    $handler->handleAdded($file);
                }
            }
        }
        $this->added = $this->removed = [];
    }

    /**
     * add consumer
     * @param $handler FileHandlerInterface
     */
    public function addHandler($handler)
    {
        $this->handlers[get_class($handler)] = $handler;
    }

    /**
     * remove consumer
     * @param $handler FileHandlerInterface
     */
    public function removeConsumer($handler)
    {
        unset($this->handlers[get_class($handler)]);
    }
}