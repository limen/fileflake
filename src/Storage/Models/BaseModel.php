<?php
/*
 * This file is part of the Fileflake package.
 *
 * (c) LI Mengxiang <limengxiang876@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Limen\Fileflake\Storage\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class BaseModel extends Eloquent
{
    protected static $columns;

    protected $connection;

    protected $collection;

    const CREATED_AT = 'createdAt';

    const UPDATED_AT = 'updatedAt';

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
    public function findById($id)
    {
        return $this->where($this->getKeyName(), $id)->take(1)->first();
    }

    /**
     * @param $checksum
     * @return static
     */
    public function findByChecksum($checksum)
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
        if (isset($attributes['_id'])) {
            unset($attributes['_id']);
        }

        return $this->where($this->getKeyName(), $id)->update($attributes);
    }

    public function increaseRefCount($id)
    {
        return $this->where($this->getKeyName(), $id)->increment('refCount');
    }

}