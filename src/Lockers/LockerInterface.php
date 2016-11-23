<?php
/**
 * @author Li Mengxiang
 * @email limengxiang876@gmail.com
 * @since 2016/6/7 9:46
 */

namespace Limen\Fileflake\Lockers;

/**
 * Locker for file to avoid conflict caused by concurrency
 * Interface LockerInterface
 * @package Fileflake\Lockers
 */
interface LockerInterface
{
    /**
     * Lock file
     * @param $fid string file Id
     * @return bool
     */
    public function lock($fid);

    /**
     * unlock file
     * @param $fid string
     */
    public function unlock($fid);

    /**
     * Check file is locked or not
     * @param $fid string file Id
     * @return bool
     */
    public function isLocked($fid);
}