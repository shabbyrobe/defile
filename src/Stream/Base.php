<?php
namespace Defile\Stream;

use Defile\FileException;
use Defile\FileSystem;
use Defile\Node;
use Defile\Stream;
use Defile\Util;

abstract class Base extends Util\Object implements Stream
{
    public $node;

    protected $mode;
    protected $eof = false;
    protected $fileSystem;

    public function __construct(FileSystem $fs, Node $node, $mode)
    {
        $this->fileSystem = $fs;
        $this->node = $node;
        $this->mode = $mode;
    }

    abstract protected function doSeek($pos, $whence);

    abstract public function flush();
    abstract public function close();
    abstract public function tell();

    /**
     * @return [string, int] Buffer, size
     */
    abstract protected function doRead($length=null);

    abstract public function write($buf);

    /**
     * Truncate or extend the stream to specific size
     * @return bool True on success
     */
    abstract public function resize($size);

    abstract public function length();

    /**
     * Must always clear the EOF marker, even if the seek position
     * is past the end of the stream.
     * @return bool True if position was updated
     */
    public function seek($pos, $whence=SEEK_SET)
    {
        $this->eof = false;
        return $this->doSeek($pos, $whence);
    }

    public function eof()
    {
        return $this->eof == true;
    }

    public function read($length=null)
    {
        if (!($this->mode & Stream::READ))
            throw FileException::ENOTREADABLE();

        list ($buf, $bytesRead) = $this->doRead($length);

        if ($length === null || $bytesRead != $length)
            $this->eof = true;

        return $buf;
    }

    public function cast($castAs)
    {
        return false;
    }

    public function __destruct()
    {
        $this->close();
    }

    public static function calculateStreamMode($fopenMode)
    {
        $streamMode = null;
        switch ($fopenMode) {
            case 'r+':
            case 'r+b':
            case 'rb+':
                $streamMode = Stream::WRITE;
            case 'r':
            case 'rb':
                $streamMode |= Stream::READ;
            break;

            case 'w+':
            case 'w+b':
            case 'wb+':
                $streamMode = Stream::READ;
            case 'w':
            case 'wb':
                $streamMode = Stream::WRITE;
            break;

            case 'a+':
            case 'a+b':
            case 'ab+':
                $streamMode = Stream::READ;
            case 'a':
            case 'ab':
                $streamMode |= Stream::WRITE;
            break;

            case 'x+':
            case 'x+b':
            case 'xb+':
                $streamMode = Stream::READ;
            case 'x':
            case 'xb':
                $streamMode |= Stream::WRITE;
            break;

            case 'c+':
            case 'c+b':
            case 'cb+':
                $streamMode = Stream::READ;
            case 'c':
            case 'cb':
                $streamMode |= Stream::WRITE;
            break;
            
            default:
                throw new \InvalidArgumentException("Unknown mode $fopenMode");
        }
        return $streamMode;
    }
}

