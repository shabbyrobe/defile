<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait FileMtime
{
    /**
     * @group filemtime
     * @group filesystemfunc
     */
    function testFileMtime()
    {
        $start = time();
        $this->put('/test', 'abcd');
        clearstatcache();

        $time = filemtime("{$this->basePath}/test");
        $this->assertTrue(abs($time - $start) <= 1, "filemtime was $time, expected approx $start");
    }

    /**
     * @group filemtime
     * @group filesystemfunc
     */
    function testFileMtimeDir()
    {
        $start = time();
        $this->fileSystem->mkdir('/test');
        clearstatcache();

        $time = filemtime("{$this->basePath}/test");
        $this->assertTrue(abs($time - $start) <= 1, "filemtime was $time, expected approx $start");
    }

    /**
     * @group filemtime
     * @group filesystemfunc
     */
    function testFileMtimeNonexistent()
    {
        $result = filemtime("{$this->basePath}/test");
        $this->assertWarning("filemtime(): stat failed for {$this->basePath}/test");
        $this->assertFalse($result);
    }

    /**
     * @group filemtime
     * @group filesystemfunc
     */
    function testFileMtimeAfterTouch()
    {
        $this->put('/test', 'abcd');
        touch("{$this->basePath}/test", 1234);
        clearstatcache();

        $time = filemtime("{$this->basePath}/test");
        $this->assertEquals(1234, $time);
    }

    /**
     * @group filemtime
     * @group filesystemfunc
     */
    function testFileMtimeAfterWrite()
    {
        $this->fileSystem->touch("/test", 9999);
        clearstatcache();
        // sanity check
        $this->assertEquals(9999, filemtime("{$this->basePath}/test"));

        file_put_contents("{$this->basePath}/test", "efgh");
        clearstatcache();

        $start = time();
        $time = filemtime("{$this->basePath}/test");
        $this->assertTrue(abs($time - $start) <= 1, "filemtime was $time, expected approx $start");
    }

    /**
     * @group filemtime
     * @group filesystemfunc
     */
    function testFileMtimeAfterRead()
    {
        $this->fileSystem->touch("/test", 9999);
        clearstatcache();
        // sanity check
        $this->assertEquals(9999, filemtime("{$this->basePath}/test"));

        file_get_contents("{$this->basePath}/test");
        clearstatcache();

        $time = filemtime("{$this->basePath}/test");
        $this->assertEquals(9999, $time);
    }

    /**
     * @group filemtime
     * @group filesystemfunc
     */
    function testFileMtimeAfterOpenAppendClose()
    {
        $this->fileSystem->touch("/test", 9999);
        clearstatcache();
        // sanity check
        $this->assertEquals(9999, filemtime("{$this->basePath}/test"));

        $h = fopen("{$this->basePath}/test", "a");
        fclose($h);
        clearstatcache();

        $time = filemtime("{$this->basePath}/test");
        $this->assertEquals(9999, $time);
    }

    /**
     * @group filemtime
     * @group filesystemfunc
     */
    function testFileMtimeAfterOpenAppend()
    {
        $this->fileSystem->touch("/test", 9999);
        clearstatcache();
        // sanity check
        $this->assertEquals(9999, filemtime("{$this->basePath}/test"));

        $h = fopen("{$this->basePath}/test", "a");
        clearstatcache();

        $time = filemtime("{$this->basePath}/test");
        $this->assertEquals(9999, $time);
    }

    /**
     * @group filemtime
     * @group filesystemfunc
     */
    function testFileMtimeAfterOpenAppendWrite()
    {
        $this->fileSystem->touch("/test", 9999);
        clearstatcache();
        // sanity check
        $this->assertEquals(9999, filemtime("{$this->basePath}/test"));

        $h = fopen("{$this->basePath}/test", "a");
        fwrite($h, 'foo');
        clearstatcache();

        $start = time();
        $time = filemtime("{$this->basePath}/test");
        $this->assertTrue(abs($time - $start) <= 1, "filemtime was $time, expected approx $start");
    }

    /**
     * @group filemtime
     * @group filesystemfunc
     */
    function testFileMtimeAfterOpenCmodeClose()
    {
        $this->fileSystem->touch("/test", 9999);
        clearstatcache();
        // sanity check
        $this->assertEquals(9999, filemtime("{$this->basePath}/test"));

        $h = fopen("{$this->basePath}/test", "c");
        fclose($h);
        clearstatcache();

        $time = filemtime("{$this->basePath}/test");
        $this->assertEquals(9999, $time);
    }

    /**
     * @group filemtime
     * @group filesystemfunc
     */
    function testFileMtimeAfterOpenCmode()
    {
        $this->fileSystem->touch("/test", 9999);
        clearstatcache();
        // sanity check
        $this->assertEquals(9999, filemtime("{$this->basePath}/test"));

        $h = fopen("{$this->basePath}/test", "c");
        clearstatcache();

        $time = filemtime("{$this->basePath}/test");
        $this->assertEquals(9999, $time);
    }

    /**
     * @group filemtime
     * @group filesystemfunc
     */
    function testFileMtimeAfterOpenCmodeWrite()
    {
        $this->fileSystem->touch("/test", 9999);
        clearstatcache();
        // sanity check
        $this->assertEquals(9999, filemtime("{$this->basePath}/test"));

        $h = fopen("{$this->basePath}/test", "c");
        fwrite($h, 'foo');
        clearstatcache();

        $start = time();
        $time = filemtime("{$this->basePath}/test");
        $this->assertTrue(abs($time - $start) <= 1, "filemtime was $time, expected approx $start");
    }
}
