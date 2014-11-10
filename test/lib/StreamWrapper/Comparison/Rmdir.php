<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait Rmdir
{
    /**
     * @group rmdir
     * @group filesystemfunc
     */
    function testRmdirEmptyDir()
    {
        $this->fileSystem->mkdir("/foo");
        $this->assertExists("/foo");
        rmdir("{$this->basePath}/foo");
        $this->assertNotExists("/foo");
    }

    /**
     * @group rmdir
     * @group filesystemfunc
     */
    function testRmdirNotEmptyFails()
    {
        $this->put("/foo/test", "abcd");
        rmdir("{$this->basePath}/foo");
        $this->assertWarning("rmdir({$this->basePath}/foo): Directory not empty");
    }

    /**
     * @group rmdir
     * @group filesystemfunc
     */
    function testRmdirFileFails()
    {
        $this->put('/foo', 'test');
        rmdir("{$this->basePath}/foo");
        $this->assertWarning("rmdir({$this->basePath}/foo): Not a directory");
    }

    /**
     * @group rmdir
     * @group filesystemfunc
     */
    function testRmdirNonexistentFails()
    {
        rmdir("{$this->basePath}/foo");
        $this->assertWarning("rmdir({$this->basePath}/foo): No such file or directory");
    }
}

