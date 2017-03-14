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

interface FileMetaContract extends MetaContract
{
    /**
     * @param FileProtocol $file
     * @return FileProtocol|null
     */
    public function getSource($file);

    /**
     * @param FileProtocol $link
     * @param FileProtocol $source
     * @return mixed
     */
    public function makeSoftLink($link, $source);
}