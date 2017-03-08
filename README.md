# Fileflake

A distributed file server utilizes mongodb(not mongo GridFS) as the storage backend for Larevel.

## Features
+ supported operations: upload, download, delete
+ distributed storage nodes
+ storage nodes load balance
+ easy to scale out (add more storage nodes on the fly)
+ file stream stored in mongodb
+ file stream is divided into chunks and the chunk size is configurable
+ files have same checksum would only store one copy

## Getting start

### Installation

Recommend to install via [composer](https://getcomposer.org/ "").

```bash
composer require "limen/fileflake"
```

### Usage

```php
use Limen\Fileflake\Config;
use Limen\Fileflake\Fileflake;
use Limen\Fileflake\Protocols\OutputFile;

class FileController
{
    protected $config = [
        Config::KEY_FILE_META_CONNECTION => 'mongodb_fileflake',    // file meta connection
        Config::KEY_FILE_META_COLLECTION => 'FileMeta',             // file meta collection
        Config::KEY_NODE_META_CONNECTION => 'mongodb_fileflake',    // node meta connection
        Config::KEY_NODE_META_COLLECTION => 'NodeMeta',             // node meta collection

        Config::KEY_FILE_CHUNK_SIZE => 4194304,                     // chunk size on byte

        // if set to true, the load balance would consider file count and file volume of each storage node,
        // or the load balance would pick one node randomly
        Config::KEY_LOAD_BALANCE_STRICT => false,

        Config::KEY_STORAGE_NODES => [
            [
                'id'         => 1,                          // storage node id, should be unique and unmodifiable
                'connection' => 'mongodb_fileflake',        // storage node connection
                'collection' => 'FileStore1',               // storage node collection
            ],
            [
                'id'         => 2,
                'connection' => 'mongodb_fileflake',
                'collection' => 'FileStore2',
            ],
        ],

        Config::KEY_LOCALIZE_DIR     => '/home/www/tmp/fileflake/local',    // the temp local files stored in this directory
        Config::KEY_LOCKER_FILES_DIR => '/home/www/tmp/fileflake/locker',   // the locker files stored in this directory
    ];
    

    public function upload()
    {
        $fileflake = new Fileflake($this->config);

        // file id is a string of 32 characters
        $fildId = $fileflake->put(
            '/tmp/abc',             // file local path
            'tulips.jpg',           // file name
            '879394',               // file size on byte
            'jpg',                  // file extension
            'mime/jpeg'             // file mime
        );
    }
    

    public function download()
    {
        $fileflake = new Fileflake($this->config);

        /** @var OutputFile $file */
        $file = $fileflake->get('5031a3057c8cff6fde3a4118187798bb');
    }
    

    public function remove()
    {
        $fileflake = new Fileflake($this->config);

        $fileflake->remove('5031a3057c8cff6fde3a4118187798bb');
    }

}
```

## Tech detail 

### File meta

Every file has a meta record. 

The reference is similar to soft link in Linux file system. 

A file is "source file" when it doesn't have a reference. On the contrary, the files which have reference are "soft links".

File's reference count (default 1) shows how many file(s) (including itself) are referring to it.

The system would only store the first one of the files which have same checksum and the others' references point to the first file.

When a soft link file is removed, its meta data would be deleted and the reference count of its source file would decrement by 1.

When a source file is removed, its reference count would decrement by 1.

When a source file's reference count decrement to 0, its meta data would be deleted and its chunks would be deleted from storage node.

#### meta fields

+ file id
+ file name
+ file checksum
+ file reference count
+ file reference
+ store node id
+ chunk ids
+ extension
+ mime type

### Storage node

The nodes store source file's chunks.

#### fields

+ chunk id
+ chunk content

### Node meta

Store the nodes meta data that would be used to load balance

#### fields

+ node id
+ file count
+ file volume

## License

MIT