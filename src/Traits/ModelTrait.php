<?php
/**
 * @author LI Mengxiang
 * @email lmx@yiban.cn
 * @since 2016/11/21 11:36
 */

namespace Limen\Fileflake\Traits;


trait ModelTrait
{
    public function filterAttributes(array $data, array $validAttributes)
    {
        $data = array_only($data, $validAttributes);
        foreach ($data as $k => $v) {
            $method = 'cast' . ucfirst($k) . 'Attribute';
            if (method_exists($this, $method)) {
                $data[$k] = $this->$method($v);
            }
        }
        return $data;
    }
}