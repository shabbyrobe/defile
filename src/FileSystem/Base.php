<?php
namespace Defile\FileSystem;

use Defile\FileException;
use Defile\Node;
use Defile\Stream;
use Defile\Util;

abstract class Base extends Util\Object implements \Defile\FileSystem
{
    public static $meta = [
        self::M_ATIME,
        self::M_MTIME,
        self::M_PERMS,
        self::M_OWNER,
        self::M_GROUP,
    ];

    protected abstract function createStream(Node $node, $streamMode);

    protected abstract function addNode(Node $node);

    public abstract function updateNode(Node $node);

    protected abstract function createNode($path, $type);

    public function exists($path) 
    {
        return $this->getNode($path) != false;
    }

    public function touch($path, $mtime=null, $atime=null)
    {
        $path = Util\Path::normalise("/$path");

        $ctime = time();
        $mtime = $mtime !== null ? $mtime : $ctime;
        $atime = $atime !== null ? $atime : $mtime;
        if (!$this->exists($path)) {
            $node = $this->createNode($path, Node::FILE);
            $this->addNode($node);
        }
        else {
            $node = $this->getNode($path);
        }

        if (!$this->setMeta($path, [self::M_MTIME=>$mtime, self::M_ATIME=>$atime]))
            throw new FileException();

        return $node;
    }

    public function open($path, $mode)
    {
        $path = Util\Path::normalise("/$path");
        $streamMode = Stream\Base::calculateStreamMode($mode);
        switch ($mode) {
            case 'r': case 'rb': case 'r+': case 'r+b': case 'rb+':
                $node = $this->getNode($path);
                if (!$node)
                    throw FileException::ENOENT();

                return $this->createStream($node, $streamMode);
            break;

            case 'x': case 'xb': case 'x+': case 'x+b': case 'xb+':
                $node = $this->getNode($path);
                if ($node) {
                    throw FileException::EEXIST();
                }
                else {
                    $node = $this->createNode($path, Node::FILE);
                    $this->addNode($node);
                }

                return $this->createStream($node, $streamMode);
            break;

            case 'w': case 'wb': case 'w+': case 'w+b': case 'wb+':
            case 'c': case 'cb': case 'c+': case 'c+b': case 'cb+':
            case 'a': case 'ab': case 'a+': case 'a+b': case 'ab+':
                if (!$this->exists($path)) {
                    $node = $this->createNode($path, Node::FILE);
                    $this->addNode($node);
                }
                else {
                    $node = $this->getNode($path);
                    if ($mode[0] == 'w') {
                        $this->setMeta($path, self::M_MTIME, time());
                    }
                }

                $stream = $this->createStream($node, $streamMode);
                if ($mode[0] == 'w')
                    $stream->resize(0);
                elseif ($mode[0] == 'a')
                    $stream->seek(0, SEEK_END);

                return $stream;
            break;

            default:
                throw new \InvalidArgumentException("Unknown mode $mode");
        }
    }
}
