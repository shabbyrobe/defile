<?php
namespace Defile;

interface Stream
{
    const READ = 1;
    const WRITE = 2;
    const READWRITE = 3;

    /**
     * Must always clear the EOF marker, even if the seek position
     * is past the end of the stream.
     * 
     * Warning: This should follow the return semantics of streamWrapper.stream_seek, not
     * fseek.
     * 
     * @return bool True if position was updated
     */
    function seek($pos, $whence=SEEK_SET);

    function flush();
    function close();

    /**
     * In plain PHP, fseek() and ftell() are undefined for append mode. For this class,
     * ftell should report the actual position
     */
    function tell();

    function getNode();

    /**
     * @return [string, int] Buffer, size
     * @throw FileException::ENOTREADABLE if !($mode & Stream::READ)
     */
    function read($length=null);

    /**
     * @return int Bytes written
     * @throw FileException::ENOTWRITABLE if !($mode & Stream::WRITE)
     */
    function write($buf);

    /**
     * Truncate or extend the stream to specific size
     * @return bool True on success
     * @throw FileException::ENOTWRITABLE if !($mode & Stream::WRITE)
     */
    function resize($size);

    function length();

    function eof();

    function cast($castAs);
}
