<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait FilePutContents
{
    /**
     * @group fileputcontents
     * @group filesystemfunc
     */
    function testFilePutContents()
    {
        $bytes = file_put_contents("{$this->basePath}/foo", "abcd");
        $this->assertEquals(4, $bytes);
    }

    /**
     * @group fileputcontents
     * @group filesystemfunc
     */
    function testFilePutContentsOverwrite()
    {
        $this->put('/foo', 'abcd');
        $bytes = file_put_contents("{$this->basePath}/foo", "efgh");
        $this->assertEquals(4, $bytes);
        $this->assertFileContains("/foo", "efgh");
    }

    /**
     * @group fileputcontents
     * @group filesystemfunc
     */
    function testFilePutContentsAppend()
    {
        $this->put('/foo', 'abcd');
        $bytes = file_put_contents("{$this->basePath}/foo", "efgh", FILE_APPEND);
        $this->assertEquals(4, $bytes);
        $this->assertFileContains("/foo", "abcdefgh");
    }

    /**
     * @group fileputcontents
     * @group filesystemfunc
     */
    function testFilePutContentsIntoNonexistentDir()
    {
        $result = file_put_contents("{$this->basePath}/nope/foo", "abcd");
        $this->assertWarning($this->tpl('openFails', ['func'=>'file_put_contents', 'file'=>'/nope/foo']));
        $this->assertFalse($result);
    }

    /**
     * @group fileputcontents
     * @group filesystemfunc
     */
    function testFilePutContentsOntoDir()
    {
        $this->fileSystem->mkdir('/dir');
        $result = file_put_contents("{$this->basePath}/dir", "abcd");
        $this->assertWarning(
            $this->tpl('openFails', ['func'=>'file_put_contents', 'file'=>'/dir', 'msg'=>'Is a directory'])
        );
        $this->assertFalse($result);
    }
}
