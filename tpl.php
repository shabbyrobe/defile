<?php
   
class TestStream
{
    public $context ;

    /**
     * @return bool
     */
    public function dir_closedir()
    {}

    /**
     * @param string $path
     * @param int $options
     * @return bool
     */
    public function dir_opendir($path, $options)
    {}

    /**
     * @return string
     */
    public function dir_readdir()
    {}

    /**
     * @return bool
     */
    public function dir_rewinddir()
    {}

    /**
     * @param string $path
     * @param int $mode
     * @param int $options
     * @return bool
     */
    public function mkdir($path, $mode, $options)
    {}

    /**
     * @param string $pathFrom
     * @param string $pathTo
     * @return bool
     */
    public function rename($pathFrom, $pathTo)
    {}

    /**
     * @param string $path
     * @param int $options
     * @return bool
     */
    public function rmdir($path, $options)
    {}

    /**
     * @param int $castAs
     * @return resource
     */
    public function stream_cast($castAs)
    {}

    /**
     * @return void
     */
    public function stream_close()
    {}

    /**
     * @return bool
     */
    public function stream_eof()
    {}

    /**
     * @return bool
     */
    public function stream_flush()
    {}

    /**
     * @param int $operation
     * @return bool
     */
    public function stream_lock()
    {}

    /**
     * @param string $path
     * @param int $option
     * @param mixed $value 
     * @return bool
     */
    public function stream_metadata($patn, $option, $value)
    {}

    /**
     * @param string $path
     * @param string $mode
     * @param int $options
     * @param string &$openedPath 
     * @return bool
     */
    public function stream_open($path, $mode, $options, &$openedPath)
    {}

    /**
     * @param int $count
     * @return string
     */
    public function stream_read()
    {}

    /**
     * @param int $offset
     * @param int $whence
     * @return bool
     */
    public function stream_seek($offset, $whence=SEEK_SET)
    {}

    /**
     * @param int $option
     * @param int $arg1
     * @param int $arg2 
     * @return bool
     */
    public function stream_set_option($option, $arg1, $arg2)
    {}

    /**
     * @return array
     */
    public function stream_stat()
    {}

    /**
     * @return int
     */
    public function stream_tell()
    {}

    /**
     * @param int $newSize
     * @return bool
     */
    public function stream_truncate($newSize)
    {}

    /**
     * @param stirng $data
     * @return int
     */
    public function stream_write($data)
    {}

    /**
     * @param string $path
     * @return bool
     */
    public function unlink($path)
    {}

    /**
     * @param string $path
     * @param int $flags
     * @return array
     */
    public function url_stat($path, $flags)
    {}
}
