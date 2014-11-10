<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait FileExists
{
    /**
     * @group fileexists
     * @group filesystemfunc
     */
    function testFileExistsFile()
    {
        $this->put('/foo', '');
        $this->assertTrue(file_exists("{$this->basePath}/foo"));
    }

    /**
     * @group fileexists
     * @group filesystemfunc
     */
    function testFileExistsDir()
    {
        $this->fileSystem->mkdir('/foo');
        $this->assertTrue(file_exists("{$this->basePath}/foo"));
    }

    /**
     * @group fileexists
     * @group filesystemfunc
     */
    function testFileExistsNonexistentFails()
    {
        $this->assertFalse(file_exists("{$this->basePath}/foo"));
    }
}

