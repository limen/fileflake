<?php
use Laravel\Lumen\Application;
use Limen\Fileflake\Config;
use Limen\Fileflake\Fileflake;
use Limen\Fileflake\Protocols\InputFile;
use Limen\Fileflake\Support\FileUtil;
use Limen\Fileflake\Support\UidGenerator;

class FileflakeTest extends \Laravel\Lumen\Testing\TestCase
{
    /** @var Fileflake */
    protected $fileflake;

    /** @var array */
    protected $config;

    public function createApplication()
    {
        $app = new Application();

        return $app;
    }

    public function setUp()
    {
        parent::setUp();

        $connection = $this->getMongoConnection();

        $this->app->register(Jenssegers\Mongodb\MongodbServiceProvider::class);

        $this->app->withEloquent();

        $this->app['config']->set("database.connections.$connection.driver", 'mongodb');
        $this->app['config']->set("database.connections.$connection.host", 'localhost');
        $this->app['config']->set("database.connections.$connection.port", 27017);
        $this->app['config']->set("database.connections.$connection.database", 'fileflake');
    }

    public function testUidGenerator()
    {
        $generator = new UidGenerator();

        $count = 10000;
        $ids = [];
        for ($i = 0; $i < $count; $i++) {
            $ids[$generator->generate()] = 1;
        }

        $this->assertEquals($count, count($ids));
    }

    public function testCreate()
    {
        $this->init();

        $fileIds = [];

        $file = $this->getTmpImage();
        $fileId = $this->fileflake->put($file);

        $fileIds[] = $fileId;

        $fileMeta = $this->fileflake->getMeta($fileId);
        $this->assertEquals($fileMeta->name, $file->name);
        $this->assertEquals($fileMeta->size, $file->size);
        $this->assertEquals($fileMeta->extension, $file->extension);
        $this->assertEquals($fileMeta->mime, $file->mime);

        $source = null;

        if (!$fileMeta->reference) {
            $this->assertEquals(1, $fileMeta->refCount);
            $this->assertEquals(count($fileMeta->chunkIds), ceil($fileMeta->size / Config::get(Config::KEY_FILE_CHUNK_SIZE)));
            $source = $fileMeta;
        } else {
            $source = $this->fileflake->getSourceMeta($fileMeta->reference);
        }
        $sourceRefCount = $source->refCount;

        $softLinkCount = rand(1,10);

        for ($i=0; $i < $softLinkCount; $i++) {
            $file = $this->getTmpImage();
            $softId = $this->fileflake->put($file);
            $softMeta = $this->fileflake->getMeta($softId);
            if (!$fileMeta->reference) {
                $this->assertEquals($softMeta->chunkIds, $fileMeta->chunkIds);
            }
            $fileIds[] = $softId;
        }

        $sourceRefCountNew = $this->fileflake->getSourceMeta($source->getId())->refCount;

        $this->assertEquals($sourceRefCount + $softLinkCount, $sourceRefCountNew);

        return $fileIds;
    }

    /**
     * @depends testCreate
     */
    public function testGet($fileIds)
    {
        $this->init();

        $uploadFile = $this->getTmpImage();

        $file = $this->fileflake->get($fileIds[0]);

        $this->assertEquals(FileUtil::checksum($file->path), FileUtil::checksum($uploadFile->path));

        $file->delete();
        $this->assertFalse(file_exists($file->path));
    }

    /**
     * @depends testCreate
     */
    public function testRemove($fileIds)
    {
        $this->init();

        $firstFile = $this->fileflake->getMeta($fileIds[0]);

        $nodeId = $firstFile->nodeId;
        $chunkIds = $firstFile->chunkIds;

        if ($firstFile->reference) {
            $sourceFile = $this->fileflake->getSourceMeta($firstFile->reference);
            $sourceRefCount = $sourceFile->refCount;
            $sourceId = $sourceFile->getId();
        } else {
            $sourceRefCount = $firstFile->refCount;
            $sourceId = $firstFile->getId();
        }

        $this->assertGreaterThanOrEqual(count($fileIds), $sourceRefCount);

        foreach ($fileIds as $id) {
            $this->fileflake->remove($id);

            $sourceRefCount--;

            if ($sourceRefCount > 0) {
                $sourceRefCountNew = $this->fileflake->getSourceMeta($sourceId)->refCount;
                $this->assertEquals($sourceRefCount, $sourceRefCountNew);
            }
        }

        $storageNode = \Limen\Fileflake\LoadBalancer::getInstance()->get($nodeId);
        if ($sourceRefCount == 0) {
            $sourceFile = $this->fileflake->getMeta($firstFile->reference);
            $this->assertNull($sourceFile);
            foreach ($chunkIds as $chunkId) {
                $chunk = $storageNode->getChunk($chunkId);
                $this->assertNull($chunk);
            }
        } else {
            $sourceFile = $this->fileflake->getMeta($firstFile->reference);
            $this->assertFalse(is_null($sourceFile));
            foreach ($chunkIds as $chunkId) {
                $chunk = $storageNode->getChunk($chunkId);
                $this->assertFalse(is_null($chunk));
            }
        }
    }

    protected function getConfig()
    {
        $mongoConnection = $this->getMongoConnection();

        $config = [
            Config::KEY_FILE_META_CONNECTION => $mongoConnection,    // file meta connection
            Config::KEY_FILE_META_COLLECTION => 'FileMeta',          // file meta collection
            Config::KEY_NODE_META_CONNECTION => $mongoConnection,    // node meta connection
            Config::KEY_NODE_META_COLLECTION => 'NodeMeta',          // node meta collection

            Config::KEY_FILE_CHUNK_SIZE     => 51200,                // chunk size in byte

            Config::KEY_FILE_CONTENT_STORAGE_NODES => [
                [
                    'id'         => 1,                              // storage node id, should be unique and unmodifiable
                    'connection' => $mongoConnection,               // storage node connection
                    'collection' => 'FileStore1',                   // storage node collection
                ],
                [
                    'id'         => 2,
                    'connection' => $mongoConnection,
                    'collection' => 'FileStore2',
                ],
            ],

            Config::KEY_LOCALIZE_DIR => $this->getLocalizeDir(),      // the temp local files stored in this directory
        ];

        return $config;
    }

    protected function init()
    {
        $this->config = $this->getConfig();

        $this->fileflake = new Fileflake($this->config);

        $this->fileflake->setUidGenerator(new UidGenerator());
    }

    protected function getTmpFileDir()
    {
        $dir = $this->getBaseDir() . '/tmp';

        if (!file_exists($dir)) {
            mkdir($dir);
        }

        return $dir;

    }

    protected function getBaseDir()
    {
        return dirname(__DIR__);
    }

    protected function getMongoConnection()
    {
        return 'mongodb';
    }

    protected function getTmpImage()
    {
        $fileName = $this->getTmpFileDir() . '/fileflake.png';

        if (!file_exists($fileName)) {
            $content = file_get_contents('http://jsonaz.com/jsonaz-shot.png');
            file_put_contents($fileName, $content);
        }

        $file = InputFile::make($fileName, 'fileflake.png', filesize($fileName), 'png', 'image/png');

        return $file;
    }

    protected function getLocalizeDir()
    {
        $path = $this->getBaseDir() . '/localize';

        if (!file_exists($path)) {
            mkdir($path);
        }

        return $path;
    }
}