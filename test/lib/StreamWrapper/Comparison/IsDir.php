<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait IsDir
{
    /**
     * @group isdir
     * @group filesystemfunc
     */
    function testIsDir()
    {
        $this->fileSystem->mkdir('/foo');
        $isDir = is_dir("{$this->basePath}/foo");
        $this->assertTrue($isDir);
    }

    /**
     * @group isdir
     * @group filesystemfunc
     */
    function testIsDirWithFile()
    {
        $this->put('/foo', 'abcd');
        $isDir = is_dir("{$this->basePath}/foo");
        $this->assertFalse($isDir);
    }

    /**
     * @group isdir
     * @group filesystemfunc
     */
    function testIsDirWithNonexistent()
    {
        $isDir = is_dir("{$this->basePath}/foo");
        $this->assertFalse($isDir);
    }
}
