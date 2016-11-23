<?php
/**
 * @author Li Mengxiang
 * @email limengxiang876@gmail.com
 * @since 2016/6/7 15:09
 */

namespace Limen\Fileflake;
use Limen\Fileflake\Exceptions\FileException;

/**
 * Config and more
 * Class Config
 * @package Fileflake
 */
class Config
{
    const KEY_STORAGE_NODES = 'storage_nodes';

    // file meta model connection name
    const KEY_FILE_META_CONNECTION = 'file_meta_connection';
    const KEY_FILE_META_COLLECTION = 'file_meta_collection';

    // node meta model connection name
    const KEY_NODE_META_CONNECTION = 'node_meta_connection';
    const KEY_NODE_META_COLLECTION = 'node_meta_collection';

    // file lockers directory
    const KEY_LOCKER_FILES_DIR = 'locker_files_dir';

    // localized files directory
    const KEY_LOCALIZE_DIR = 'localize_dir';

    const KEY_FILE_CHUNK_SIZE = 'chunk_size';

    const KEY_LOAD_BALANCE_STRICT = 'load_balance_strict';

    public static $config;

    /**
     * @param array $config
     */
    public static function load(array $config)
    {
        static::$config = $config;
    }

    public static function set($key, $value)
    {
        static::$config[$key] = $value;
    }

    public static function get($key, $default = null)
    {
        return isset(static::$config[$key]) ? static::$config[$key] : $default;
    }

    /**
     * Get file node model connection name which is related with node id
     * @param $id
     * @return array|false
     */
    public static function getFileNodeConfig($id)
    {
        $nodes = static::get(static::KEY_STORAGE_NODES);
        foreach ($nodes as $item) {
            if ($item['id'] == $id) {
                return $item;
            }
        }

        FileException::pop("The connection for storage node $id is not set. Check the configuration field {" . static::KEY_STORAGE_NODES . "}");
    }

}