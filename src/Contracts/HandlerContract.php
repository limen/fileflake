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

use Limen\Fileflake\Protocols\FileProtocol;

/**
 * File handler interface.
 * The subclass should handle the involved files properly.
 * Interface HandlerInterface
 * @package Fileflake\Storage
 */
interface HandlerContract
{
    /**
     * Handle added file
     * @param FileProtocol $file
     * @return mixed
     */
    public function handleAdded($file);

    /**
     * Handle removed file
     * @param FileProtocol $file
     * @return mixed
     */
    public function handleRemoved($file);

    /**
     * Handle touched file
     * @param FileProtocol $file
     * @return mixed
     */
    public function handleTouched($file);
}
