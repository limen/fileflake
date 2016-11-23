<?php
namespace Limen\Fileflake\Protocols;
/**
 * @author Li Mengxiang
 * @email limengxiang876@gmail.com
 * @since 2016/6/16 14:38
 *
 * @property string   path      file path on local system
 * @property string   name      file name
 * @property int      size      file size in byte
 * @property string   extension file extension
 *
 */
class OutputFile
{
    protected $attributes = [];

    public function __construct($name, $path, $size = null, $extension = null)
    {
        $this->attributes['name'] = $name;
        $this->attributes['path'] = $path;
        $this->attributes['size'] = $size;
        $this->attributes['extension'] = $extension;
    }

    public function __get($attr)
    {
        return isset($this->attributes[$attr]) ? $this->attributes[$attr] : null;
    }

    public function toArray()
    {
        return $this->attributes;
    }

}