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

use Limen\Fileflake\Contracts\UidGeneratorContract;

/**
 * Generate file id
 * Class UidGenerator
 * @package App\Library
 */
class UidGenerator implements UidGeneratorContract
{
    /**
     * Get unique file id
     * @return string
     */
    public function generate()
    {
        return md5(uniqid());
    }
}