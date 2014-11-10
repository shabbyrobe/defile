<?php
namespace Defile;

class StreamRegistry extends Util\Object
{
    public static $instance;

    public static $defaultClass = 'Defile\StreamWrapper';

    public $schemes = [];

    public static function instance()
    {
        if (!static::$instance) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    function add($scheme, $name, FileSystem $fs, $class=null)
    {
        if (!isset($this->schemes[$scheme])) {
            stream_wrapper_register($scheme, $class ?: static::$defaultClass);
            $this->schemes[$scheme] = [];
        }
        $this->schemes[$scheme][$name] = $fs; 
    }

    function remove($scheme, $name)
    {
        if (isset($this->schemes[$scheme][$name])) {
            unset($this->schemes[$scheme][$name]);
            if (count($this->schemes[$scheme]) == 0) {
                stream_wrapper_unregister($scheme);
                unset($this->schemes[$scheme]);
            }
        }
    }

    function clear()
    {
        foreach ($this->schemes as $scheme=>$names) {
            foreach ($names as $name=>$fs) {
                $this->remove($scheme, $name);
            }
        }
    }

    function get($scheme, $name)
    {
        return isset($this->schemes[$scheme][$name])
            ? $this->schemes[$scheme][$name]
            : null
        ;
    }
}

