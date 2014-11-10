<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait FileGetContents
{
    /**
     * @group filegetcontents
     * @group filesystemfunc
     */
    function testFileGetContentsFile()
    {
        $this->put('/foo', 'abcd');
        $this->assertEquals('abcd', file_get_contents("{$this->basePath}/foo"));
    }

    /**
     * @group filegetcontents
     * @group filesystemfunc
     */
    function testFileGetContentsFileOffset()
    {
        $this->put('/foo', '0123456789');
        $out = file_get_contents("{$this->basePath}/foo", false, null, 5);
        $this->assertEquals('56789', $out);
    }

    /**
     * @group filegetcontents
     * @group filesystemfunc
     */
    function testFileGetContentsFileMaxlen()
    {
        $this->put('/foo', '0123456789');
        $out = file_get_contents("{$this->basePath}/foo", false, null, null, 5);
        $this->assertEquals('01234', $out);
    }

    /**
     * @group filegetcontents
     * @group filesystemfunc
     */
    function testFileGetContentsFileOffsetMaxlen()
    {
        $this->put('/foo', '0123456789');
        $out = file_get_contents("{$this->basePath}/foo", false, null, 2, 5);
        $this->assertEquals('23456', $out);
    }

    /**
     * @group filegetcontents
     * @group filesystemfunc
     */
    function testFileGetContentsNonexistent()
    {
        $result = file_get_contents("{$this->basePath}/foo");
        $this->assertWarning($this->tpl('openFails', ['func'=>'file_get_contents', 'file'=>'/foo']));
        $this->assertFalse($result);
    }

    /**
     * @group filegetcontents
     * @group filesystemfunc
     * @see Fopen::testFopenReadDir
     */
    function testFileGetContentsDir()
    {
        $this->fileSystem->mkdir('/foo');
        $result = file_get_contents("{$this->basePath}/foo");
        $this->assertEquals('', $result);
    }
}
