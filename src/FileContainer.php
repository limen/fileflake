<?php
/*
 * This file is part of the Fileflake package.
 *
 * (c) LI Mengxiang <limengxiang876@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Limen\Fileflake;

use Limen\Fileflake\Contracts\FileContainerContract;
use Limen\Fileflake\Contracts\HandlerContract;
use Limen\Fileflake\Protocols\FileProtocol;

/**
 * One request may involved more than one file.
 * The container would store the involved files temporarily.
 * The container would invoke file handlers and flush the involved files at the end.
 * Class FileContainer
 * @package Fileflake\Storage\Containers
 */
class FileContainer implements FileContainerContract
{
    /** @var static */
    protected static $instance;

    /** @var FileProtocol[] */
    protected $added = [];

    /** @var FileProtocol[] */
    protected $removed = [];

    /** @var FileProtocol[] */
    protected $touched = [];

    /** @var HandlerContract[] */
    protected $handlers = [];

    protected function __construct()
    {
        //
    }

    /**
     * @return FileContainer
     */
    public static function getInstance()
    {
        if (!static::$instance) {
            $className = Config::get(Config::KEY_CONTRACT_CONCRETE_MAP . '.' . FileContainerContract::class);
            if ($className) {
                static::$instance = new $className();
            } else {
                static::$instance = new static();
            }
        }

        return static::$instance;
    }

    /**
     * "add" file
     * @param $file FileProtocol
     */
    public function add($file)
    {
        $this->added[$file->id] = $file;
    }

    /**
     * "remove" file
     * @param $file FileProtocol
     */
    public function remove($file)
    {
        $this->removed[$file->id] = $file;
    }

    /**
     * @param FileProtocol $file
     */
    public function touch($file)
    {
        $this->touched[$file->id] = $file;
    }

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
            if ($this->touched) {
                foreach ($this->touched as $file) {
                    $handler->handleTouched($file);
                }
            }
        }

        $this->added = $this->removed = [];
    }

    /**
     * @param HandlerContract $handler
     * @return $this
     */
    public function addHandler($handler)
    {
        $this->handlers[get_class($handler)] = $handler;

        return $this;
    }

    /**
     * @param HandlerContract|string $handler
     * @return $this
     */
    public function removeHandler($handler)
    {
        if ($handler instanceof HandlerContract) {
            unset($this->handlers[get_class($handler)]);
        } else {
            unset($this->handlers[$handler]);
        }

        return $this;
    }
}