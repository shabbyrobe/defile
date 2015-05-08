<?php
namespace Defile\FileSystem;

use Defile\Access;
use Defile\FileException;
use Defile\Node;
use Defile\Stream;
use Defile\Util;

class Memory extends Base
{
    private $tree = null;

    public function __construct($tree=null)
    {
        $this->tree = $tree ?: (object)[
            'node'=>$this->createNode('/', Node::DIR),
            'data'=>[],
            'len'=>0,
        ];
    }

    private function descendInternal($path)
    {
        $cur = $this->tree;
        $parts = Util\Path::split($path);

        yield $cur;
        foreach ($parts as $part) {
            if ($cur->node->type != Node::DIR) {
                throw FileException::ENOTDIR();
            }
            if (!isset($cur->data[$part])) {
                throw FileException::ENOENT();
            }
            $cur = $cur->data[$part]; 
            yield $cur;
        }
    }

    public function descend($path)
    {
        foreach ($this->descendInternal($path) as $intNode) {
            yield $intNode->node;
        }
    }

    public function iterate($path)
    {
        $path = Util\Path::normalise("/$path");
        $dir = $this->getInternalNode($path);
        if (!$dir) {
            throw FileException::ENOENT();
        }
        if ($dir->node->type != Node::DIR) {
            throw FileException::ENOTDIR();
        }

        // if this class function simply yields by itself, the guard
        // clauses are never executed until the generator starts its
        // iteration.
        $iterator = function() use ($dir) {
            foreach ($dir->data as $name=>$node) {
                yield $name;
            }
        };
        return $iterator();
    }

    public function setMeta($path, $meta, $value=null)
    {
        $path = Util\Path::normalise("/$path");
        if ($value) {
            $meta = [$meta=>$value];
        }
        if (array_diff(array_keys($meta), static::$meta)) {
            throw new \InvalidArgumentException();
        }
        $node = $this->getNode($path);
        if (!$node) {
            throw FileException::ENOENT();
        }
        if (isset($meta[static::M_PERMS])) {
            $node->perms = $meta[static::M_PERMS] & 07777;
        }
        if (isset($meta[static::M_GROUP])) {
            $node->group = $meta[static::M_GROUP];
        }
        if (isset($meta[static::M_OWNER])) {
            $node->owner = $meta[static::M_OWNER];
        }
        if (isset($meta[static::M_ATIME])) {
            $atime = $meta[static::M_ATIME];
            $node->atime = $atime === true ? time() : $atime;
        }
        if (isset($meta[static::M_MTIME])) {
            $mtime = $meta[static::M_MTIME];
            $node->mtime = $mtime === true ? time() : $mtime;
        }

        $node->ctime = time();

        return true;
    }

    public function rm($path)
    {
        $path = Util\Path::normalise("/$path");
        $dir = dirname($path);
        $base = basename($path);
        $dirMemNode = $this->getInternalNode($dir);

        if (!$dirMemNode || !isset($dirMemNode->data[$base])) {
            throw FileException::ENOENT();
        }
        $memNode = $dirMemNode->data[$base];
        if ($memNode->node->type == Node::DIR) {
            throw FileException::EISDIR();
        }
        unset($dirMemNode->data[$base]);
        --$dirMemNode->len;
    }

    public function rmdir($path)
    {
        $path = Util\Path::normalise("/$path");
        $parentPath = dirname($path);
        $base = basename($path);
        $parentMemNode = $this->getInternalNode($parentPath);

        if (!$parentMemNode || !isset($parentMemNode->data[$base])) {
            throw FileException::ENOENT();
        }
        $memNode = $parentMemNode->data[$base];
        if ($memNode->node->type != Node::DIR) {
            throw FileException::ENOTDIR();
        }
        if ($memNode->len > 0) {
            throw FileException::ENOTEMPTY();
        }
        unset($parentMemNode->data[$base]);

        return true;
    }

    public function mkdir($path, $mode=0777, $options=null)
    {
        $path = Util\Path::normalise("/$path");
        $target = $this->getInternalNode($path);
        if ($target) {
            throw FileException::EEXIST();
        }
        if ($options & STREAM_MKDIR_RECURSIVE) {
            $bits = "";
            $make = false;
            foreach (Util\Path::split($path) as $part) {
                $bits .= "/$part";
                if (!$make) {
                    $node = $this->getInternalNode($bits);
                    if (!$node) {
                        $make = true;
                    }
                }
                if ($make) {
                    if (!$this->mkdir($bits, $mode, $options & ~STREAM_MKDIR_RECURSIVE)) {
                        throw new \Exception();
                    }
                }
            }
            return true;
        }
        else {
            $dir = $this->getInternalNode(dirname($path));
            if (!$dir) {
                throw FileException::ENOENT();
            } elseif ($dir->node->type != Node::DIR) {
                throw FileException::ENOTDIR();
            }

            $node = $this->createNode($path, Node::DIR, ['perms'=>$mode]);
            $dir->data[basename($path)] = (object)['node'=>$node, 'data'=>[], 'len'=>0];
            return true;
        }
    }

    public function rename($from, $to)
    {
        $from = Util\Path::normalise("/$from");
        $to = Util\Path::normalise("/$to");

        $fromInfo = pathinfo($from);
        $toInfo = pathinfo($to);

        $fromNode = $this->getInternalNode($from);
        if (!$fromNode)
            throw FileException::ENOENT();

        $fromParent = $this->getInternalNode($fromInfo['dirname']);

        $toNode = $this->getInternalNode($to);
        if ($toNode && $toNode->type == Node::DIR) {
            $toParent = $toNode; 
        } else {
            $toParent = $this->getInternalNode($toInfo['dirname']);
        }
        if (!$toParent) {
            throw FileException::ENOENT();
        }
        $fromNode->node->path = $to;
        $toParent->data[$toInfo['basename']] = $fromNode;
        unset($fromParent->data[$fromInfo['basename']]);

        return true;
    }
    
    protected function createNode($path, $mode, $args=[])
    {
        $args['inode'] = crc32(uniqid('', true));
        $args = array_merge([$path, $mode], $args);
        $node = Node::construct($args);
        $node->atime = $node->mtime = $node->ctime = time();
        return $node;
    }

    private function getInternalNode($path)
    {
        $cur = null;
        try {
            foreach ($this->descendInternal($path) as $cur);
        }
        catch (FileException $ex) {
            if ($ex->getCode() == FileException::ENOENT) {
                return null;
            } else {
                throw $ex;
            }
        }
        return $cur;
    }

    public function getNode($path)
    {
        $node = $this->getInternalNode($path);
        if ($node) {
            return $node->node;
        }
    }

    protected function addNode(Node $node)
    {
        $info = pathinfo($node->path);
        if ($info['dirname'] == '.') {
            throw new \InvalidArgumentException("Absolute paths only - '{$node->path}' was relative");
        }
        $parentNode = $this->getInternalNode($info['dirname']);
        if (!$parentNode) {
            throw FileException::ENOENT("Parent {$info['dirname']} not found");
        }
        if ($node->type == Node::DIR) {
            $node = (object)['node'=>$node, 'data'=>[], 'len'=>0];
        } else {
            $node = (object)['node'=>$node, 'data'=>(object)['data'=>''], 'len'=>0];
        }
        $parentNode->len++;
        $parentNode->data[$info['basename']] = $node;
    }

    public function updateNode(Node $node)
    {
        // no-op: everything's a reference!
    }

    protected function createStream(Node $node, $streamMode)
    {
        $memNode = $this->getInternalNode($node->path);
        if (!$memNode && $streamMode & Stream::WRITE) {
            $this->addNode($node);
            $memNode = $this->getInternalNode($node->path);
        }
        if (!$memNode) {
            return false;
        }
        if ($memNode->node->type == Node::DIR) {
            throw FileException::EISDIR();
        }
        return new Stream\Memory($this, $node, $streamMode, $memNode->data);
    }
}
