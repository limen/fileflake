<?php
/**
 * @author Li Mengxiang
 * @email limengxiang876@gmail.com
 * @since 2016/5/13 13:12
 */

namespace Limen\Fileflake\Support;

/**
 * Generate file id
 * Class UidGenerator
 * @package App\Library
 */
class UidGenerator
{
    /**
     * Get unique file id
     * @param $salt
     * @return string
     */
    public static function id($salt)
    {
        return md5(uniqid() . $salt);
    }
}