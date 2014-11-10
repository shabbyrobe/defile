<?php
namespace Defile\FileSystem;

use Defile\FileException;
use Defile\FileSystem;
use Defile\Node;
use Defile\Stream;
use Defile\Util;

/**
 * TODO: create some tests that ensure warnings raised by FS functions
 * if the guard clauses pass but something changes the filesystem between
 * are captured and wrapped. E.g.:
 * 
 *   if (!exists($file))
 *       throw FileException::ENOENT();
 *   // something deletes file in a separate process.
 *   // now this call fails with its own warning:
 *   $h = fopen($file, ...); 
 * 
 * Tried to do it using SplFileObject which throws exceptions like we need
 * but sometimes the PHP devs just get it so wrong: 
 * https://bugs.php.net/bug.php?id=36289&edit=1
 */
class LocalFile implements FileSystem
{
    public function __construct($basePath)
    {
        if (!Util\Path::isabs($basePath))
            throw new \InvalidArgumentException("Path $basePath was not absolute");

        $this->basePath = Util\Path::normalise($basePath);
        if (!$this->basePath)
            throw new \UnexpectedValueException("Sanity check - $basePath did not normalise ({$this->basePath})");
    }

    private function getFullPath($path)
    {
        $fullPath = Util\Path::normalise("{$this->basePath}/$path");
        if (!$this->isInsideBase($fullPath))
            throw new \InvalidArgumentException();
        return $fullPath;
    }

    private function getDir($fullPath)
    {
        $dir = Util\Path::normalise(dirname($fullPath));
        if (!$dir || !$this->isInsideBase($dir))
            throw new \InvalidArgumentException();
        return $dir;
    }

    private function isInsideBase($normalisedPath)
    {
        return strpos($normalisedPath, $this->basePath) === 0;
    }

    public function mkdir($path, $mode=0777, $options=null)
    {
        $fullPath = $this->getFullPath($path);
        return mkdir($fullPath, $mode, ($options & STREAM_MKDIR_RECURSIVE == STREAM_MKDIR_RECURSIVE));
    }

    public function rm($path)
    {
        $fullPath = $this->getFullPath($path);
        if (!file_exists($fullPath))
            throw FileException::ENOENT();
        if (is_dir($fullPath))
            throw FileException::EISDIR();

        return unlink($fullPath);
    }

    public function rmdir($path)
    {
        $fullPath = $this->getFullPath($path);
        if (!file_exists($fullPath))
            throw FileException::ENOENT();
        elseif (!is_dir($fullPath))
            throw FileException::ENOTDIR();

        $dh = opendir($fullPath);
        try {
            while ($item = readdir($dh)) {
                if ($item != '.' && $item != '..')
                    throw FileException::ENOTEMPTY();
            }
        }
        finally {
            closedir($dh);
        }
        return rmdir($fullPath);
    }

    public function setMeta($path, $meta, $value=null)
    {
        $fullPath = $this->getFullPath($path);
        if (!file_exists($fullPath))
            throw FileException::ENOENT();

        if ($value)
            $meta = [$meta=>$value];
 
        if (!$meta)
            throw new \InvalidArgumentException();

        if (array_diff(array_keys($meta), Base::$meta))
            throw new \InvalidArgumentException();

        if (isset($meta[static::M_PERMS])) {
            if (!chmod($fullPath, $meta[static::M_PERMS] & 07777))
                return false;
        }

        if (isset($meta[static::M_GROUP])) {
            if (!chgrp($fullPath, $meta[static::M_GROUP]))
                return false;
        }

        if (isset($meta[static::M_OWNER])) {
            if (!chgrp($fullPath, $meta[static::M_OWNER]))
                return false;
        }

        $atime = isset($meta[static::M_ATIME]) ? $meta[static::M_ATIME] : null;
        $mtime = isset($meta[static::M_MTIME]) ? $meta[static::M_MTIME] : null;
        if ($atime !== null || $mtime !== null) {
            if ($mtime === null) {
                clearstatcache();
                $mtime = filemtime($fullPath);
            }

            if ($atime === null) {
                if (!touch($fullPath, $mtime))
                    return false;
            }
            else {
                if (!touch($fullPath, $mtime, $atime))
                    return false;
            }
        }

        return true;
    }

    public function iterate($path)
    {
        $fullPath = $this->getFullPath($path);
        if (!file_exists($fullPath))
            throw FileException::ENOENT();
        elseif (!is_dir($fullPath))
            throw FileException::ENOTDIR();

        $gen = function() use ($fullPath) {
            $h = opendir($fullPath);
            while (($file = readdir($h)) !== false) {
                if ($file != '.' && $file != '..')
                    yield $file;
            }
            closedir($h);
        };
        return $gen();
    }

    public function getNode($path)
    {
        $fullPath = $this->getFullPath($path);

        clearstatcache();
        if (file_exists($fullPath)) {
            $stat = stat($fullPath);
            return Node::createFromStat($path, $stat);
        }
    }

    public function open($path, $mode)
    {
        $fullPath = $this->getFullPath($path);
        $dir = $this->getDir($fullPath);

        $node = $this->getNode($path);
        if ($node && $node->type == Node::DIR)
            throw FileException::EISDIR();

        switch ($mode) {
            case 'r': case 'r+': case 'rb': case 'r+b': case 'rb+':
                if (!$node)
                    throw FileException::ENOENT();
            break;

            case 'x': case 'x+': case 'xb': case 'x+b': case 'xb+':
                if ($node)
                    throw FileException::ENOENT();
                elseif (!is_dir($dir))
                    throw FileException::ENOENT();
            break;

            case 'a': case 'a+': case 'ab': case 'a+b': case 'ab+':
            case 'c': case 'c+': case 'cb': case 'c+b': case 'cb+':
            case 'w': case 'w+': case 'wb': case 'w+b': case 'wb+':
                if (!is_dir($dir))
                    throw FileException::ENOENT();
            break;
            
            default:
                throw new \InvalidArgumentException("Unknown mode $mode");
        }
        $h = fopen($fullPath, $mode);
        if (!$h)
            throw new \UnexpectedValueException();

        $streamMode = Stream\Base::calculateStreamMode($mode);
        return new Stream\LocalFile($path, $mode, $streamMode, $h);
    }

    public function exists($path)
    {
        $fullPath = $this->getFullPath($path);
        return file_exists($fullPath);
    }

    public function touch($path, $mtime=null, $atime=null)
    {
        $fullPath = $this->getFullPath($path);
        if (!file_exists($fullPath)) {
            $dir = $this->getDir($fullPath);
            if (!file_exists($dir))
                throw FileException::ENOENT();
            elseif (!is_dir($dir))
                throw FileException::ENOTDIR();
        }

        if ($mtime !== null && $atime !== null)
            return touch($fullPath, $mtime, $atime);
        elseif ($mtime !== null)
            return touch($fullPath, $mtime);
        else
            return touch($fullPath);
    }

    public function rename($from, $to)
    {
        $fullPathFrom = $this->getFullPath($from);
        $fullPathTo = $this->getFullPath($to);

        $fromNode = $this->getNode($from);
        if (!$fromNode)
            throw FileException::ENOENT();

        $toNode = $this->getNode($to);
        if ($toNode && $toNode->type == Node::DIR) {
            $toParent = $toNode; 
            $fullPathTo = $this->getFullPath($toNode->path."/".basename($from));
        }
        else {
            $toParent = $this->getNode(dirname($to));
        }

        if (!$toParent)
            throw FileException::ENOENT();

        $ret = rename($fullPathFrom, $fullPathTo);
        return $ret;
    }
}
