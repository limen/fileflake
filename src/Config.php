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

use Limen\Fileflake\Exceptions\Exception;

/**
 * Config and more
 * Class Config
 * @package Fileflake
 */
class Config
{
    const KEY_FILE_CONTENT_STORAGE_NODES = 'file_content_storage_nodes';

    // file meta model connection name
    const KEY_FILE_META_CONNECTION = 'file_meta_connection';
    const KEY_FILE_META_COLLECTION = 'file_meta_collection';

    // node meta model connection name
    const KEY_NODE_META_CONNECTION = 'node_meta_connection';
    const KEY_NODE_META_COLLECTION = 'node_meta_collection';

    // localized files directory
    const KEY_LOCALIZE_DIR = 'localize_dir';

    const KEY_FILE_CHUNK_SIZE = 'chunk_size';

    const KEY_LOAD_BALANCE_STRICT = 'load_balance_strict';

    const KEY_CONTRACT_CONCRETE_MAP = 'contract_concrete_map';

    public static $config;

    /**
     * @param array $config
     */
    public static function load(array $config)
    {
        static::$config = $config;

        foreach (static::getDefaults() as $key => $value) {
            if (!isset(static::$config[$key]) || !is_array($value)) {
                static::$config[$key] = $value;
            } else {
                static::$config[$key] = array_merge(static::$config[$key], $value);
            }
        }
    }

    public static function set($key, $value)
    {
        array_set(static::$config, $key, $value);
    }

    public static function get($key, $default = null)
    {
        return array_get(static::$config, $key, $default);
    }

    /**
     * @param $config
     */
    public static function addNode($config)
    {
        static::$config[static::KEY_FILE_CONTENT_STORAGE_NODES][] = $config;
    }

    /**
     * Get file node model connection name which is related with node id
     * @param $id
     * @return array|false
     * @throws Exception
     */
    public static function getFileContentStorageConfig($id)
    {
        $nodes = static::get(static::KEY_FILE_CONTENT_STORAGE_NODES);

        foreach ($nodes as $item) {
            if ($item['id'] == $id) {
                return $item;
            }
        }

        throw new Exception("The connection of storage node $id not set.");
    }

    /**
     * default config values
     * @return array
     */
    protected static function getDefaults()
    {
        return [
            static::KEY_LOAD_BALANCE_STRICT   => false,
            Config::KEY_CONTRACT_CONCRETE_MAP => [
                \Limen\Fileflake\Contracts\UidGeneratorContract::class  => \Limen\Fileflake\Support\UidGenerator::class,
                \Limen\Fileflake\Contracts\BalancerContract::class      => \Limen\Fileflake\LoadBalancer::class,
                \Limen\Fileflake\Contracts\LockContract::class          => \Limen\Fileflake\Lock\RedLock::class,
                \Limen\Fileflake\Contracts\FileContainerContract::class => \Limen\Fileflake\FileContainer::class,
                \Limen\Fileflake\Contracts\FileMetaContract::class      => \Limen\Fileflake\Storage\FileMetaStorage::class,
            ],
        ];
    }

}