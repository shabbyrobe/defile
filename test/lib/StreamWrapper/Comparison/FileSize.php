<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait FileSize
{
    /**
     * @group filesize
     * @group filesystemfunc
     */
    function testFileSize()
    {
        $this->put('/test', 'abcd');
        clearstatcache();
        $this->assertEquals(4, filesize("{$this->basePath}/test"));
    }

    /**
     * @group filesize
     * @group filesystemfunc
     */
    function testFileSizeAfterWriteBeforeClose()
    {
        $this->put('/test', 'abcd');
        clearstatcache();

        $h = fopen("{$this->basePath}/test", "a");
        fwrite($h, "efgh");

        $this->assertEquals(8, filesize("{$this->basePath}/test"));
    }

    /**
     * @group filesize
     * @group filesystemfunc
     */
    function testFileSizeNonexistent()
    {
        $result = filesize("{$this->basePath}/test");
        $this->assertWarning("filesize(): stat failed for {$this->basePath}/test");
        $this->assertFalse($result);
    }

    /**
     * @group filesize
     * @group filesystemfunc
     */
    function testFileSizeDir()
    {
        $this->fileSystem->mkdir("/test");
        $result = filesize("{$this->basePath}/test");

        // filesize is sort of irrelevant with a dir - in linux, you'll get 
        // the number of bytes the directory entry actually occupies in the file
        // system (not including the contents).
        // maybe we should expect that it's greather than or equal to 1, the argument
        // being that a directory entry is always going to take up some space.
        $this->assertGreaterThanOrEqual(0, $result);
    }
}
