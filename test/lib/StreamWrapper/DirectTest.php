<?php
namespace Defile\Test\StreamWrapper;

use Defile\Node;

/**
 * @group direct
 * @group streamwrapper
 */
class DirectTest extends TestCase implements \Defile\FileSystem
{
    use Comparison\All;

    function setUp()
    {
        parent::setUp();
        $this->basePath = sys_get_temp_dir()."/".uniqid('defile-directtest-', true);
        mkdir($this->basePath);

        $this->templates['openFails'] ='{func}({basePath}{file}): failed to open stream: {msg}';
        $this->templates['openDirFails'] ='{func}({basePath}{file}): failed to open dir: {msg}';
    }

    function tearDown()
    {
        try {
            parent::tearDown();
        }
        finally {
            $dir = new \RecursiveDirectoryIterator($this->basePath);
            foreach (new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::CHILD_FIRST) as $i) {
                if ($i->getFilename() == '.' || $i->getFilename() == '..') continue;
                if ($i->isDir())
                    rmdir($i->getPathname());
                else
                    unlink($i->getPathname());
            }
            rmdir($this->basePath);
        }
    }

    function createFileSystem()
    {
        return $this;
    }

    public function updateNode(Node $node)
    {
        throw new \Exception("Nope");
    }

    public function mkdir($path, $mode=0777, $options=null)
    {
        return mkdir("{$this->basePath}/$path", $mode, $options & STREAM_MKDIR_RECURSIVE);
    }

    function rm($path)
    {
        return unlink("{$this->basePath}/$path");
    }

    function rmdir($path)
    {
        return rmdir("{$this->basePath}/$path");
    }

    function setMeta($path, $meta, $value=null)
    {
        $fullPath = "{$this->basePath}/$path";
        if ($value)
            $meta = [$meta=>$value];
 
        if (!$meta)
            throw new \InvalidArgumentException();

        if (array_diff(array_keys($meta), self::$meta))
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

        $node->ctime = time();

        return true;
    }

    function iterate($path)
    {
        $h = opendir($path);
        while (($file = readdir($h)) !== false) {
            yield $file;
        }
        closedir($h);
    }

    function getNode($path)
    {
        clearstatcache();
        $type = filetype("{$this->basePath}/$path");
        if ($type == 'dir')
            $type = Node::DIR;
        elseif ($type == 'file')
            $type = Node::FILE;
        else
            $type = null;

        if ($type) {
            $node = Node::createFromStat($path, stat("{$this->basePath}/$path"));
            return $node;
        }
    }

    function canRead($path)
    {}

    function canWrite($path)
    {}

    function canExecute($path)
    {}

    function open($path, $mode)
    {
        return fopen("{$this->basePath}/$path", $mode);
    }

    function exists($path)
    {
        return file_exists("{$this->basePath}/$path");
    }

    function touch($path, $mtime=null, $atime=null)
    {
        if ($mtime == null)
            return touch("{$this->basePath}/$path");
        elseif ($atime == null)
            return touch("{$this->basePath}/$path", $mtime);
        else
            return touch("{$this->basePath}/$path", $mtime, $atime);
    }

    function rename($from, $to)
    {
        return rename("{$this->basePath}/$from", "{$this->basePath}/$to");
    }

    protected function put($node, $contents)
    {
        $path = $node instanceof Node ? $node->path : $node;
        if (!is_dir("{$this->basePath}/".dirname($path)))
            mkdir("{$this->basePath}/".dirname($path), 0777, true);

        $path = "{$this->basePath}/".ltrim($path, '/');
        file_put_contents($path, $contents);
    }

    public function assertFileContains($file, $contents)
    {
        $result = file_get_contents($this->basePath.$file);
        $this->assertEquals($contents, $result);
    }

    public function assertExists($file)
    {
        $exists = file_exists($this->basePath.$file);
        $this->assertTrue($exists);
    }

    public function assertNotExists($file)
    {
        $exists = file_exists($this->basePath.$file);
        $this->assertFalse($exists);
    }
}
