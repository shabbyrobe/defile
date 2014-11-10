<?php
namespace Defile\Stream;

use Defile\Buffer;
use Defile\FileException;
use Defile\FileSystem;
use Defile\Node;
use Defile\Stream;

class Memory extends Base
{
    private $pos = 0;
    private $buffer;

    public function __construct(FileSystem $fs, Node $node, $mode, \stdClass $buffer)
    {
        parent::__construct($fs, $node, $mode);
        if (!isset($buffer->data) || !is_string($buffer->data))
            throw new \InvalidArgumentException("Buffer must be stdclass string in 'data'");
        $this->buffer = $buffer;
    }

    protected function doSeek($pos, $whence=SEEK_SET)
    {
        $old = $this->pos;
        if (SEEK_SET == $whence) {
            $this->pos = $pos;
        }
        elseif (SEEK_END == $whence) {
            // see testFseekEndWriteAfterEnd... why does it work for SEEK_SET and not this?
            $this->pos = $this->node->length + $pos;
        }
        elseif (SEEK_CUR == $whence) {
            $this->pos = $this->pos + $pos;
        }
        else {
            throw new \InvalidArgumentException();
        }

        // see DirectTest::testFseekCurNegPastStart - seeking before start doesn't update
        if ($this->pos < 0)
            $this->pos = $old;

        return $this->pos != $old;
    }

    public function getNode()
    {
        return $this->node;
    }

    public function length()
    {
        return $this->node->length;
    }

    public function flush()
    {
    }

    public function close()
    {}

    public function tell()
    {
        return $this->pos;
    }

    protected function doRead($length=null)
    {
        if ($length)
            $ret = substr($this->buffer->data, $this->pos, $length);
        else
            $ret = substr($this->buffer->data, $this->pos);

        $this->fileSystem->setMeta($this->node->path, FileSystem::M_ATIME, true);

        $bytesRead = strlen($ret);
        $this->pos += $bytesRead;
        return [$ret, $bytesRead];
    }

    public function write($buf)
    {
        if (!($this->mode & Stream::WRITE))
            throw FileException::ENOTWRITABLE();

        $len = strlen($buf);
        if ($len) {
            $this->fileSystem->setMeta($this->node->path, FileSystem::M_MTIME, true);
        }

        if ($this->pos > $this->node->length) {
            $nulls = $this->pos - $this->node->length;
            $this->buffer->data .= str_repeat("\x00", $nulls);
            $this->node->length = $this->pos;
        }

        if ($this->pos < $this->node->length) {
            $newBuf = substr($this->buffer->data, 0, $this->pos).$buf;
            if ($this->pos + $len < $this->node->length) {
                $newBuf .= substr($this->buffer->data, $this->pos + $len);
                $this->pos += $len;
            }
            else {
                $this->node->length = $this->pos + $len;
                $this->pos = $this->node->length;
            }
            $this->buffer->data = $newBuf;
        }
        else {
            $this->pos += $len;
            $this->node->length = max($this->pos, $this->node->length);
            $this->buffer->data .= $buf;
        }
        return $len;
    }

    public function resize($size)
    {
        if (!($this->mode & Stream::WRITE))
            throw FileException::ENOTWRITABLE();

        // ftruncate manual page states that the file pointer is not changed,
        // hence this is disabled:
        // $this->pos = min($this->pos, $size);

        if ($size > $this->node->length) {
            $this->buffer->data = str_pad($this->buffer->data, $size, "\x00");
        }
        elseif ($size < $this->node->length) {
            $new = substr($this->buffer->data, 0, $size);
            if ($new === false) {
                throw new \UnexpectedValueException(
                    "Buffer of length {$this->node->length} could not be truncated to size $size"
                );
            }
            $this->buffer->data = $new;
        }
        $this->node->length = $size;

        return true;
    }
}
