<?php
/*
 * This file is part of the Fileflake package.
 *
 * (c) LI Mengxiang <limengxiang876@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Limen\Fileflake\Contracts;

interface FileContainerContract
{
    /**
     * @param HandlerContract $handler
     * @return mixed
     */
    public function addHandler($handler);

    /**
     * @param HandlerContract $handler
     * @return mixed
     */
    public function removeHandler($handler);

    /**
     * Dump the files and call handlers
     * @return mixed
     */
    public function dump();
}