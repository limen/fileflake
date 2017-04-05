<?php
/*
 * This file is part of the Fileflake package.
 *
 * (c) LI Mengxiang <limengxiang876@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
    public static function getFileStream($fileName, $offset = null, $maxLen = null)
    {
        $content = file_get_contents($fileName, null, null, $offset, $maxLen);

        return base64_encode($content);
    }

    /**
     * @param $stream string file stream
     * @param $fileName string file name to save $str
     */
    public static function appendStreamToFile($stream, $fileName)
    {
        $fileContent = base64_decode($stream);

        file_put_contents($fileName, $fileContent, FILE_APPEND);
    }

    /**
     * Get checksum of file
     * @param $file
     * @return string
     */
    public static function checksum($file)
    {
        return md5_file($file);
    }
}