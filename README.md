# Fileflake

A distributed file server utilizes mongodb(not mongo GridFS) as the storage backend for Larevel.

[![Build Status](https://travis-ci.org/limen/fileflake.svg?branch=master)](https://travis-ci.org/limen/fileflake)
[![Packagist](https://img.shields.io/packagist/l/limen/fileflake.svg?maxAge=2592000)](https://packagist.org/packages/limen/fileflake)

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

$config = [
    Config::KEY_FILE_META_CONNECTION => 'mongodb',          // required, file meta connection
    Config::KEY_FILE_META_COLLECTION => 'FileMeta',         // required, file meta collection
    Config::KEY_NODE_META_CONNECTION => 'mongodb',          // required, node meta connection
    Config::KEY_NODE_META_COLLECTION => 'NodeMeta',         // required, node meta collection

    Config::KEY_FILE_CHUNK_SIZE     => 51200,               // required, chunk size in byte

    // if set to true, the load balance would consider file count and file volume of each storage node,
    // or the load balance would pick one node randomly
    Config::KEY_LOAD_BALANCE_STRICT => true,                // optional, default value is false

    // required
    Config::KEY_FILE_CONTENT_STORAGE_NODES => [
        [
            'id'         => 1,                              // storage node id, should be unique and unmodifiable
            'connection' => 'mongodb',                      // storage node connection
            'collection' => 'FileStorage1',                 // storage node collection
        ],
        [
            'id'         => 2,
            'connection' => 'mongodb',
            'collection' => 'FileStorage2',
        ],
    ],

    Config::KEY_LOCALIZE_DIR     => '/tmp/fileflake/localize',      // required, the temp local files stored in this directory

    // optional, see default values below
    Config::KEY_CONTRACT_CONCRETE_MAP => [
        \Limen\Fileflake\Contracts\UidGeneratorContract::class => \Limen\Fileflake\Support\UidGenerator::class,
        \Limen\Fileflake\Contracts\BalancerContract::class => \Limen\Fileflake\LoadBalancer::class,
        \Limen\Fileflake\Contracts\LockContract::class => \Limen\Fileflake\Lock\RedLock::class,
        \Limen\Fileflake\Contracts\FileContainerContract::class => \Limen\Fileflake\FileContainer::class,
        \Limen\Fileflake\Contracts\FileMetaContract::class => \Limen\Fileflake\Storage\FileMetaStorage::class,
    ],
];

$filePath = '/path/to/file';
$file = new \Limen\Fileflake\Protocols\InputFile($filePath, 'fileflake.png', filesize($filePath), 'png', 'image/png');
$fileflake = new \Limen\Fileflake\Fileflake($config);

/** @var string $fileId md5 */
$fileId = $fileflake->put($file);
/** @var \Limen\Fileflake\Protocols\OutputFile $fileMeta no local copy */
$fileMeta = $fileflake->getMeta($fileId);
/** @var \Limen\Fileflake\Protocols\OutputFile $fileMeta have a local copy */
$localFile = $fileflake->get($fileId);
/** @var string $localPath path of local copy */
$localPath = $localFile->path;

// remove file
$fileflake->remove($fileId);

$fileflake->get($fileId);           // return null
```

## Concepts 

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