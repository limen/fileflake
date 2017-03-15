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

use Limen\Fileflake\Contracts\FileMetaContract;
use Limen\Fileflake\Contracts\HandlerContract;
use Limen\Fileflake\Contracts\LockContract;
use Limen\Fileflake\Contracts\UidGeneratorContract;
use Limen\Fileflake\Exceptions\Exception;
use Limen\Fileflake\Handlers\FileContentStorageHandler;
use Limen\Fileflake\Handlers\NodeMetaHandler;
use Limen\Fileflake\Lock\RedLock;
use Limen\Fileflake\Protocols\InputFile;
use Limen\Fileflake\Protocols\OutputFile;
use Limen\Fileflake\Storage\FileMetaStorage;

class Fileflake
{
    /** @var UidGeneratorContract */
    protected $uidGenerator;

    /** @var FileMetaStorage */
    protected $fileMetaStorage;

    /** @var LockContract */
    protected $lock;

    /** @var HandlerContract[] */
    protected $handlers = [];

    public function __construct(array $config)
    {
        Config::load($config);
        $this->setUidGenerator();
        $this->setLock();
        $this->setFileMetaStorage();
        $this->setDefaultHandlers();
    }

    /**
     * Config fileflake
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function config($key, $value)
    {
        Config::set($key, $value);

        return $this;
    }

    /**
     * @param UidGeneratorContract $generator
     * @return $this
     * @throws Exception
     */
    public function setUidGenerator($generator = null)
    {
        if ($generator) {
            $this->uidGenerator = $generator;
        } else {
            $className = Config::get(Config::KEY_CONTRACT_CONCRETE_MAP . '.' . UidGeneratorContract::class);
            if (!$className) {
                throw new Exception(UidGeneratorContract::class . '\'s concrete class not set.');
            }
            $this->uidGenerator = new $className();
        }

        return $this;
    }

    /**
     * @param null $storage
     * @return $this
     * @throws Exception
     */
    public function setFileMetaStorage($storage = null)
    {
        if ($storage) {
            $this->fileMetaStorage = $storage;
        } else {
            $className = Config::get(Config::KEY_CONTRACT_CONCRETE_MAP . '.' . FileMetaContract::class);
            if (!$className) {
                throw new Exception(FileMetaContract::class . '\'s concrete class not set.');
            }
            $this->fileMetaStorage = new $className();
        }

        return $this;
    }

    /**
     * @param LockContract|null $lock
     * @return $this
     * @throws Exception
     */
    public function setLock($lock = null)
    {
        if ($lock) {
            $this->lock = $lock;
        } else {
            $className = Config::get(Config::KEY_CONTRACT_CONCRETE_MAP . '.' . LockContract::class, RedLock::class);
            if (!$className) {
                throw new Exception(LockContract::class . '\'s concrete class not set.');
            }
            $this->lock = new $className();
        }

        return $this;
    }

    /**
     * @param HandlerContract $handler
     * @return $this
     */
    public function addHandler(HandlerContract $handler)
    {
        FileContainer::getInstance()->addHandler($handler);

        return $this;
    }

    /**
     * @param $handler
     * @return $this
     */
    public function removeHandler($handler)
    {
        FileContainer::getInstance()->removeHandler($handler);

        return $this;
    }

    /**
     * @param $id
     * @return OutputFile|null
     */
    public function getMeta($id)
    {
        return $this->fileMetaStorage->get($id);
    }

    /**
     * Get source file meta
     * @param $sourceId
     * @return OutputFile|null
     */
    public function getSourceMeta($sourceId)
    {
        return $this->fileMetaStorage->getSourceMeta($sourceId);
    }

    /**
     * @param InputFile $inputFile
     * @return mixed
     */
    public function put($inputFile)
    {
        $inputFile->setId($this->uidGenerator->generate());

        $sourceFile = $this->fileMetaStorage->getSource($inputFile);

        if ($sourceFile) {
            $this->lock->lock($sourceFile->getId());
            $this->fileMetaStorage->makeSoftLink($inputFile, $sourceFile);
            $this->lock->unlock($sourceFile->getId());
        } else {
            $inputFile->nodeId = LoadBalancer::getInstance()->select()->getId();
            $this->fileMetaStorage->add($inputFile);
        }

        FileContainer::getInstance()->dump();

        $this->lock->unlock($inputFile->getId());

        return $inputFile->getId();
    }

    /**
     * @param $id string
     * @return OutputFile|null
     */
    public function get($id)
    {
        $file = $this->getMeta($id);

        $storage = LoadBalancer::getInstance()->get($file->nodeId);

        $file->localize($storage);

        FileContainer::getInstance()->touch($file);

        FileContainer::getInstance()->dump();

        return $file;
    }

    /**
     * Remove file
     * @param $id string file id
     * @return bool|null
     */
    public function remove($id)
    {
        $file = $this->fileMetaStorage->get($id);

        if ($file === null) {
            return false;
        }

        $this->fileMetaStorage->remove($file);

        FileContainer::getInstance()->dump();

        $this->lock->unlock($id);

        return true;
    }

    /**
     * @return $this
     */
    protected function setDefaultHandlers()
    {
        FileContainer::getInstance()->addHandler(new FileContentStorageHandler());

        FileContainer::getInstance()->addHandler(new NodeMetaHandler());

        return $this;
    }
}