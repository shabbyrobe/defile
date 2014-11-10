<?php
namespace Defile;

class Node extends Util\Object
{
    const FILE = 1;
    const DIR = 2;

    public $type;
    public $perms;
    public $owner;
    public $group;
    public $path;
    public $mtime;
    public $atime;
    public $ctime;
    public $length;
    public $inode;

    public function __construct($path, $type=self::FILE, $perms=null, $owner=null, $group=null, $inode=null)
    {
        $this->path = Util\Path::normalise("/$path");
        $this->type = $type;
        $this->owner = $owner;
        $this->inode = $inode;
        $this->group = $group;

        if ($perms)
            $this->perms = $perms;
        elseif ($type == self::DIR)
            $this->perms = 0777;
        else
            $this->perms = 0666;
    }

    public static function createFromStat($path, $stat)
    {
        $type = null;
        $mode = $stat['mode'];
        if ($mode & 040000)
            $type = Node::DIR;
        elseif ($mode & 0100000)
            $type = Node::FILE;
        else
            throw new \UnexpectedValueException("Unexpected mode: ".decoct($mode));

        $node = new static($path, $type);
        $node->inode = $stat['ino'];
        $node->owner = $stat['uid'];
        $node->group = $stat['gid'];
        $node->length = $stat['size'];
        $node->atime = $stat['atime'];
        $node->mtime = $stat['mtime'];
        $node->ctime = $stat['ctime'];

        return $node;
    }
    
    public function getFullMode()
    {
        $mode = $this->perms;
        if ($this->type == Node::DIR) 
            $mode |= 040000;
        else
            $mode |= 0100000;
        return $mode;
    }

    public function stat()
    {
        $stat = [];
        $stat[0]  = $stat['dev']     = 0;
        $stat[1]  = $stat['ino']     = $this->inode;
        $stat[2]  = $stat['mode']    = $this->getFullMode();
        $stat[3]  = $stat['nlink']   = 1;
        $stat[4]  = $stat['uid']     = $this->owner;
        $stat[5]  = $stat['gid']     = $this->group;
        $stat[6]  = $stat['rdev']    = 0;
        $stat[7]  = $stat['size']    = $this->length;
        $stat[8]  = $stat['atime']   = $this->atime;
        $stat[9]  = $stat['mtime']   = $this->mtime;
        $stat[10] = $stat['ctime']   = $this->ctime;
        $stat[11] = $stat['blksize'] = -1;
        $stat[12] = $stat['blocks']  = -1;
        return $stat;
    }
}
