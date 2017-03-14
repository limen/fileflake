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

use Limen\Fileflake\Contracts\BalancerContract;
use Limen\Fileflake\Exceptions\Exception;
use Limen\Fileflake\Storage\FileContentStorage;
use Limen\Fileflake\Storage\Models\NodeMetaModel;

/**
 * Class LoadBalancer
 * @package Fileflake\Storage\Routers
 */
class LoadBalancer implements BalancerContract
{
    /** @var static */
    protected static $instance;
    private $availableNodes = [];
    private $usedNodes = [];

    protected function __construct()
    {
        $this->availableNodes = Config::get(Config::KEY_FILE_CONTENT_STORAGE_NODES);
    }

    public static function getInstance()
    {
        if (!static::$instance) {
            $className = Config::get(Config::KEY_CONTRACT_CONCRETE_MAP . '.' . BalancerContract::class);

            static::$instance = $className ? new $className() : new static();
        }

        return static::$instance;
    }

    /**
     * Make load balance and get storage node id
     * pick a storage node randomly
     * @return FileContentStorage
     * @throws Exception
     */
    public function select()
    {
        if (Config::get(Config::KEY_LOAD_BALANCE_STRICT)) {
            $this->getUsedNodes();
            $nodeId = $this->pickStrictly();
        } else {
            $nodeId = $this->pickRandomly();
        }

        if ($nodeId === false) {
            throw new Exception('Fail to select storage node.');
        }

        return $this->get($nodeId);
    }

    public function get($id)
    {
        return new FileContentStorage($id);
    }

    private function pickRandomly()
    {
        $nodeIds = array_column($this->availableNodes, 'id');
        return $nodeIds[array_rand($nodeIds)];
    }

    private function pickStrictly()
    {
        $usedNodeIds = array_column($this->usedNodes, '_id');
        $avlNodeIds = array_column($this->availableNodes, 'id');

        $emptyNodeIds = array_diff($avlNodeIds, $usedNodeIds);

        if ($emptyNodeIds) {
            return $emptyNodeIds[array_rand($emptyNodeIds)];
        }

        $node = [];

        foreach ($this->usedNodes as $item) {
            if ($node == []) {
                $node = $item;
            } elseif ($this->compareNode($node, $item)) {
                $node = $item;
            }
        }

        return $node ? $node['_id'] : false;
    }

    private function getUsedNodes()
    {
        $this->usedNodes = (new NodeMetaModel())->getUsedNodes();
    }

    /**
     * @param $node1
     * @param $node2
     * @return bool
     */
    private function compareNode($node1, $node2)
    {
        if ($node1['volume'] >= $node2['volume']) {
            return true;
        } elseif ($node1['volume'] == $node2['volume']) {
            return $node1['fileCount'] >= $node2['fileCount'] ? true : false;
        } else {
            return false;
        }
    }

}