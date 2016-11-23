<?php
/**
 * @author Li Mengxiang
 * @email limengxiang876@gmail.com
 * @since 2016/5/17 11:39
 */

namespace Limen\Fileflake\Support;

/**
 * Class FileUtil
 * @package Limen\Fileflake\Support
 */
class FileUtil
{
    const PACK_FORMAT = 'H*';

    /**
     * @param $fileName string file name to parse
     * @param null|int $offset
     * @param null|int $maxLen
     * @return bool|string
     */
    public static function fileToString($fileName, $offset = null, $maxLen = null)
    {
        $content = file_get_contents($fileName, null, null, $offset, $maxLen);
        $strArray = unpack(static::PACK_FORMAT, $content);
        return $strArray ? array_shift($strArray) : false;
    }

    /**
     * @param $str string file content
     * @param $fileName string file name to save $str
     */
    public static function appendStringToFile($str, $fileName)
    {
        $fileContent = pack(static::PACK_FORMAT, $str);
        file_put_contents($fileName, $fileContent, FILE_APPEND);
    }

    public static function fileChecksum($file)
    {
        return md5_file($file);
    }

}