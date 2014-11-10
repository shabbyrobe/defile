<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait FileType
{
    /**
     * @group filetype
     * @group filesystemfunc
     */
    function testFileTypeFile()
    {
        $this->put('/test', 'abcd');
        $result = filetype("{$this->basePath}/test");
        $this->assertEquals('file', $result);
    }

    /**
     * @group filetype
     * @group filesystemfunc
     */
    function testFileTypeDir()
    {
        $this->fileSystem->mkdir('/test');
        $result = filetype("{$this->basePath}/test");
        $this->assertEquals('dir', $result);
    }

    /**
     * @group filetype
     * @group filesystemfunc
     */
    function testFileTypeNonexistentFileInExistingDir()
    {
        $this->fileSystem->mkdir('/test');
        $result = filetype("{$this->basePath}/test/nope");
        $this->assertWarning("filetype(): Lstat failed for {$this->basePath}/test/nope");
        $this->assertFalse($result);
    }

    /**
     * @group filetype
     * @group filesystemfunc
     */
    function testFileTypeInNonexistentDir()
    {
        $result = filetype("{$this->basePath}/test/nope");
        $this->assertWarning("filetype(): Lstat failed for {$this->basePath}/test/nope");
        $this->assertFalse($result);
    }
}
