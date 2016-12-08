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

This library could be found on [Packagist](https://packagist.org/packages/limen/redmodel "") for an easier management of projects dependencies using [Composer](https://getcomposer.org/ "").

### 使用

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

### 存储架构

#### 文件元数据

每个文件都有一个元数据。

元数据的“引用”类似于Linux文件系统的软链接。

“引用计数”表示目前有多个文件指向当前文件（源文件）。

拥有相同签名的多个文件只存储一个拷贝，它们通过“引用”值与源文件产生关联。

当删除一个软链接文件时，删除文件元数据，软链接文件指向的源文件的引用计数减1。
当删除一个源文件时，源文件的引用计数减1。
当一个文件的引用计数为0时，删除该文件的元数据，并将该文件从后端存储中删除。

+ 文件id
+ 文件名
+ 文件签名
+ 引用计数
+ 引用（源文件id）
+ 存储节点id
+ 分块id
+ 扩展名
+ mime

#### 存储节点

+ 分块id
+ 分块内容

#### 节点元数据
存放存储节点元数据，用于负载均衡

+ 文件数量
+ 文件占用空间
