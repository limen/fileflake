# Fileflake

为Laravel定制的分布式文件存储服务，使用mongodb作为后端存储引擎。

## 特性
+ 支持的操作：上传，下载，删除
+ 分布式的文件存储节点
+ 存储节点负载均衡
+ 易于横向扩展（添加存储节点）
+ 文件流存储于mongodb
+ 文件流分块存储，块大小可配置
+ 拥有同样签名的文件只存储一个拷贝

## 上手

### 安装

推荐通过[composer](https://getcomposer.org/ "")安装.

```bash
composer require "limen/fileflake"
```

### 使用

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
$localPath = $localPath->path;

// remove file
$fileflake->remove($fileId);

$fileflake->get($fileId);           // return null
```

## 理念

### 文件元数据

每个文件都有一个元数据。

“引用”的概念类似于Linux文件系统中的“软连接”。

文件的“引用计数”（默认值1）表示目前有多少个文件（包括自身）指向该文件。

对于拥有相同签名的多个文件，系统只存储第一个文件，其余文件通过“引用”指向第一个文件。

当删除一个软链接文件时，删除该文件元数据，软链接文件指向的源文件的引用计数减1。

当删除一个源文件时，源文件的引用计数减1。

当一个源文件的引用计数为0时，删除该文件的元数据，并将该文件的分块从后端存储中删除。

#### 元数据的属性

+ 文件id
+ 文件名
+ 文件签名
+ 引用计数
+ 引用（源文件id）
+ 存储节点id
+ 分块id
+ 扩展名
+ mime

### 存储节点

存储文件的分块

#### 属性

+ 分块id
+ 分块内容

### 节点元数据

存放存储节点元数据，用于负载均衡

#### 属性

+ 文件数量
+ 文件占用空间
