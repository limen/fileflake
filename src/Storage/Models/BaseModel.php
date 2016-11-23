<?php
/**
 * @author Li Mengxiang
 * @email limengxiang876@gmail.com
 * @since 2016/6/7 13:47
 */

namespace Limen\Fileflake\Storage\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class BaseModel extends Eloquent
{

    protected static $columns;

    protected $connection;

    protected $collection;

    public function filterColumns(array $data, array $validColumns)
    {
        $data = array_only($data, $validColumns);
        foreach ($data as $k => $v) {
            $method = 'cast' . ucfirst($k) . 'Attribute';
            if (method_exists($this, $method)) {
                $data[$k] = $this->$method($v);
            }
        }
        return $data;
    }

    /**
     * @param $name string collection name
     * @return $this
     */
    public function setCollection($name)
    {
        $this->collection = $name;

        return $this;
    }

    /**
     * @param $id
     * @return static
     */
    public function ofId($id)
    {
        return $this->where($this->getKeyName(), $id)->take(1)->first();
    }

    /**
     * @param $checksum
     * @return static
     */
    public function ofChecksum($checksum)
    {
        return $this->where('checksum', $checksum)->take(1)->first();
    }

    /**
     * @param $id string|string[]
     * @return mixed
     */
    public function deleteById($id)
    {
        if (is_array($id)) {
            return $this->whereIn($this->getKeyName(), $id)->delete();
        } else {
            return $this->where($this->getKeyName(), $id)->delete();
        }
    }

    /**
     * @param $id
     * @param array $attributes
     * @return mixed
     */
    public function updateOne($id, array $attributes)
    {
        return $this->where($this->getKeyName(), $id)->update($attributes);
    }

}