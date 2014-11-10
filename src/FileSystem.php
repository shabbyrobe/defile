<?php
namespace Defile;

/**
 * All functions that accept a $path should allow values without trailing
 * slashes, but normalise to include them::
 * 
 *     $path = \Defile\Util\Path::normalise("/$path");
 */
interface FileSystem
{
    const M_ATIME = 1;
    const M_MTIME = 2;
    const M_GROUP = 3;
    const M_OWNER = 4;
    const M_USER = 4;
    const M_PERMS = 5;

    /**
     * Do not raise FileException::ENOENT fron this.
     * @return Defile\Node (null if not found)
     */
    function getNode($path);

    /**
     * @return Defile\Stream
     * @throw Defile\FileException::ENOENT File not found
     * @throw Defile\FileException::EISDIR Tried to open directory
     */
    function open($path, $mode); 

    function exists($path);

    function touch($path, $mtime=null, $atime=null);

    /**
     * Can pass a key and a value as arguments, or multiple bits of metadata in
     * a hash to the meta argument.
     * Meta (or keys) must be one of the FileSystem::M_* constants
     */
    function setMeta($path, $meta, $value=null);

    function rename($from, $to);
    function mkdir($dir, $mode=0777, $options=null);

    /**
     * @return void
     */
    function rm($path);

    /**
     * @return void
     */
    function rmdir($path);

    function iterate($path);
}
