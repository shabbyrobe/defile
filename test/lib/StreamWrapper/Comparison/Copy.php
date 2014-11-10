<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait Copy
{
    /**
     * @group copy
     * @group filesystemfunc
     */
    function testCopyFileToFile()
    {
        $this->put('/foo', 'abcd');
        $result = copy("{$this->basePath}/foo", "{$this->basePath}/bar");
        $this->assertTrue($result);
        $this->assertFileContains('/foo', 'abcd');
        $this->assertFileContains('/bar', 'abcd');
    }

    /**
     * @group copy
     * @group filesystemfunc
     */
    function testCopyNonexistentFileToFile()
    {
        $this->fileSystem->mkdir('/bar');

        $result = copy("{$this->basePath}/foo", "{$this->basePath}/bar/foo");
        $this->assertWarning($this->tpl('openFails', ['func'=>'copy', 'file'=>'/foo']));
        $this->assertFalse($result);
    }

    /**
     * @group copy
     * @group filesystemfunc
     */
    function testCopyFileToFileInNonexistentDirFails()
    {
        $this->put('/foo', 'abcd');

        $result = copy("{$this->basePath}/foo", "{$this->basePath}/nope/foo");
        $this->assertWarning($this->tpl('openFails', ['func'=>'copy', 'file'=>'/nope/foo']));
        $this->assertFalse($result);
    }

    /**
     * @group copy
     * @group filesystemfunc
     */
    function testCopyNonexistentFileToFileInNonexistentDirFails()
    {
        $result = copy("{$this->basePath}/foo", "{$this->basePath}/bar");
        $this->assertWarning($this->tpl('openFails', ['func'=>'copy', 'file'=>'/foo']));
        $this->assertFalse($result);
    }

    /**
     * @group copy
     * @group filesystemfunc
     */
    function testCopyFileToDirFails()
    {
        $this->put('/foo', 'abcd');
        $this->fileSystem->mkdir('/bar'); 

        $result = copy("{$this->basePath}/foo", "{$this->basePath}/bar");
        $this->assertWarning("copy(): The second argument to copy() function cannot be a directory");
        $this->assertFalse($result);
    }

    /**
     * @group copy
     * @group filesystemfunc
     */
    function testCopyDirFails()
    {
        $this->fileSystem->mkdir('/foo'); 

        $result = copy("{$this->basePath}/foo", "{$this->basePath}/bar");
        $this->assertWarning("copy(): The first argument to copy() function cannot be a directory");
        $this->assertfalse($result);
    }
}
