<?php
/**
 * @author Li Mengxiang
 * @email limengxiang876@gmail.com
 * @since 2016/6/7 9:49
 */

namespace Limen\Fileflake\Storage\Meta;

use Limen\Fileflake\Protocols\InputFile;
use Limen\Fileflake\Storage\Models\NodeMetaModel;

class NodeMeta
{
    /**
     * @param $file InputFile
     * @return mixed
     */
    public function add($file)
    {
        return (new NodeMetaModel())->add($file);
    }

    /**
     * @param $file InputFile
     * @return mixed
     */
    public function remove($file)
    {
        return (new NodeMetaModel())->remove($file);
    }

}