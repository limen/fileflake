<?php
/**
 * @author Li Mengxiang
 * @email limengxiang876@gmail.com
 * @since 2016/6/7 11:57
 */

namespace Limen\Fileflake\Lockers;

use Limen\Fileflake\Config;

/**
 * locker utilizes local file system
 * Class FileLocker
 * @package Fileflake\Lockers
 */
class FileLocker implements LockerInterface
{
    protected $maxRetryTimes = 10;
    private $prefix = 'fileflake_locker_';

    public function lock($fid)
    {
        $locked = false;

        for ($i = 0; $i < $this->maxRetryTimes; $i++) {
            if (!$this->isLocked($fid)) {
                touch($this->getLockerFile($fid));
                $locked = true;
            }
        }

        return $locked;
    }

    public function unlock($fid)
    {
        $this->removeLockerFile($fid);
    }

    public function isLocked($fid)
    {
        return file_exists($this->getLockerFile($fid));
    }

    private function getLockerFile($fid)
    {
        return Config::get(Config::KEY_LOCKER_FILES_DIR) . '/' . $this->prefix . $fid;
    }

    private function removeLockerFile($fid)
    {
        unlink($this->getLockerFile($fid));
    }
}