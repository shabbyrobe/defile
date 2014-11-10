<?php
namespace Defile\Test\StreamWrapper\Comparison;

use Defile\Node;

trait Dir
{
    /**
     * @group dir
     * @group directoryfunc
     */
    function testDir()
    {
        $this->put('/a/b', 'a');
        $this->put('/a/c', 'a');

        // unfortunately, DirectTest will always yield a ".." entry as it is in a /tmp dir, 
        // so we must test a layer deeper than root.
        $dir = dir("{$this->basePath}/a"); 
        $this->assertInstanceOf(\Directory::class, $dir);
        
        $entries = [$dir->read(), $dir->read(), $dir->read(), $dir->read()];
        $this->assertFalse($dir->read());

        sort($entries);
        $this->assertEquals(['.', '..', 'b', 'c'], $entries);
        $dir->close();
    }

    /**
     * @group dir
     * @group directoryfunc
     */
    function testDirNonexistent()
    {
        $dir = dir("{$this->basePath}/a"); 
        $this->assertWarning($this->tpl('openDirFails', ['func'=>'dir', 'file'=>'/a']));
        $this->assertFalse($dir);
    }

    /**
     * @group dir
     * @group directoryfunc
     */
    function testDirFileFails()
    {
        $this->put('/a', 'abcd');
        $dir = dir("{$this->basePath}/a"); 
        $this->assertWarning($this->tpl('openDirFails', ['func'=>'dir', 'file'=>'/a', 'msg'=>'Not a directory']));
        $this->assertFalse($dir);
    }

    /**
     * @group dir
     * @group directoryfunc
     */
    function testDirRewind()
    {
        $this->put('/a/b', 'a');
        $this->put('/a/c', 'a');

        // unfortunately, DirectTest will always yield a ".." entry as it is in a /tmp dir, 
        // so we must test a layer deeper than root.
        $dir = dir("{$this->basePath}/a"); 
        $this->assertInstanceOf(\Directory::class, $dir);
        
        $entries1 = [$dir->read(), $dir->read(), $dir->read(), $dir->read()];
        $this->assertFalse($dir->read());

        $dir->rewind();
        $entries2 = [$dir->read(), $dir->read(), $dir->read(), $dir->read()];
        $this->assertFalse($dir->read());

        sort($entries1);
        sort($entries2);
        $this->assertEquals($entries1, $entries2);
        $dir->close();
    }
}
