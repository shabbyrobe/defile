<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait IsFile
{
    /**
     * @group isfile
     * @group filesystemfunc
     */
    function testIsFile()
    {
        $this->put('/foo', 'abcd');
        $isFile = is_file("{$this->basePath}/foo");
        $this->assertTrue($isFile);
    }

    /**
     * @group isfile
     * @group filesystemfunc
     */
    function testIsFileWithDir()
    {
        $this->fileSystem->mkdir('/foo');
        $isFile = is_file("{$this->basePath}/foo");
        $this->assertFalse($isFile);
    }

    /**
     * @group isfile
     * @group filesystemfunc
     */
    function testIsFileWithNonexistent()
    {
        $isFile = is_file("{$this->basePath}/foo");
        $this->assertFalse($isFile);
    }
}
