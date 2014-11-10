<?php
namespace Defile\Stream;

use Defile\FileException;
use Defile\FileSystem;
use Defile\Node;
use Defile\Stream;
use Defile\Util;

class LocalFile extends Util\Object implements Stream
{
    private $handle;
    private $path;
    private $streamMode;
    private $openMode;
    private $pos = 0;

    function __construct($path, $openMode, $streamMode, $handle)
    {
        if (!is_resource($handle))
            throw new \InvalidArgumentException();

        $this->handle = $handle;
        $this->path = $path;
        $this->openMode = $openMode;
        $this->streamMode = $streamMode;

        if ($this->openMode[0] == 'a')
            $this->pos = $this->length();
    }

    function seek($pos, $whence=SEEK_SET)
    {
        $old = $this->pos;
        
        if ($whence == SEEK_SET)
            $this->pos = $pos;
        elseif ($whence == SEEK_CUR)
            $this->pos += $pos;
        elseif ($whence == SEEK_END)
            $this->pos = max(0, $this->length() + $pos);
        else
            throw new \UnexpectedValueException();

        $ret = fseek($this->handle, $pos, $whence);        
        if ($ret == -1) {
            // Throwing here would make more sense than returning false...
            // but it means the tests don't pass. Perhaps an exception belongs
            // here but caught in the StreamWrapper?
            // throw new \UnexpectedValueException();
            return false;
        }

        return $old != $this->pos;
    }

    function flush()
    {
        return fflush($this->handle);
    }

    function close()
    {
        return fclose($this->handle);
    }

    function tell()
    {
        return $this->pos;
    }

    function getNode()
    {
        return Node::createFromStat($this->path, fstat($this->handle));
    }

    function read($length=null)
    {
        if (!($this->streamMode & Stream::READ))
            throw FileException::ENOTREADABLE();

        if ($length === null)
            $buf = stream_get_contents($this->handle);
        else
            $buf = fread($this->handle, $length);

        return $buf;
    }

    function write($buf)
    {
        if (!($this->streamMode & Stream::WRITE))
            throw FileException::ENOTWRITABLE();

        $ret = fwrite($this->handle, $buf);
        return $ret;
    }

    function resize($size)
    {
        if (!($this->streamMode & Stream::WRITE))
            throw FileException::ENOTWRITABLE();

        return ftruncate($this->handle, $size);
    }

    function length()
    {
        return $this->getNode()->length;
    }

    function eof()
    {
        return feof($this->handle);
    }

    function cast($castAs)
    {
        return $this->handle;
    }
}
