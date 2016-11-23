<?php
/**
 * @author Li Mengxiang
 * @email limengxiang876@gmail.com
 * @since 2016/6/7 10:27
 */

namespace Limen\Fileflake\Exceptions;

class FileException extends \Exception
{
    protected $data;

    /**
     * @param string $message
     * @param $code
     */
    public static function pop($message, $code = 0)
    {
        throw new static($message, $code);
    }
}