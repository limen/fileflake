<?php
/**
 * @author Li Mengxiang
 * @email limengxiang876@gmail.com
 * @since 2016/6/7 10:00
 */

namespace Limen\Fileflake\Storage;
use Limen\Fileflake\Config;
use Limen\Fileflake\Exceptions\FileException;
use Limen\Fileflake\Storage\Models\NodeMetaModel;

/**
 * If there were more than one storage nodes, we should make load balance.
 * Class StorageNodeSelector
 * @package Fileflake\Storage\Routers
 */
class StorageNodeSelector
{
    private $availableNodes = [];

    private $usedNodes = [];

    public function __construct()
    {
        $this->availableNodes = Config::get(Config::KEY_STORAGE_NODES);
    }

    /**
     * Make load balance and get storage node id
     * pick a storage node randomly
     * @return int
     */
    public function getNodeId()
    {
        if (Config::get(Config::KEY_LOAD_BALANCE_STRICT)) {
            $this->getUsedNodes();
            $nodeId = $this->pickStrictly();
        } else {
            $nodeId = $this->pickOneNodeRandomly();
        }

        if ($nodeId === false) {
            FileException::pop('Fail to select storage node.');
        }

        return $nodeId;
    }

    /**
     * @param $nodeId int
     * @return StorageNode
     */
    public function getNodeById($nodeId)
    {
        return new StorageNode($nodeId);
    }

    private function pickOneNodeRandomly()
    {
        $nodeIds = array_column($this->availableNodes, 'id');
        return $nodeIds[array_rand($nodeIds)];
    }

    private function pickStrictly()
    {
        $usedNodeIds = array_column($this->usedNodes, '_id');
        $avlNodeIds = array_column($this->availableNodes, 'id');

        $unusedNodeIds = array_diff($avlNodeIds, $usedNodeIds);
        if ($unusedNodeIds) {
            return $unusedNodeIds[array_rand($unusedNodeIds)];
        }

        $node = [];

        foreach ($this->usedNodes as $item) {
            if ($node == []) {
                $node = $item;
            } elseif ($item['volume'] <= $node['volume']
                || $item['fileCount'] <= $node['volume']
            ) {
                $node = $item;
            }
        }

        return $node ? $node['_id'] : false;
    }

    private function getUsedNodes()
    {
        $this->usedNodes = (new NodeMetaModel())->getUsedNodes();
    }

}