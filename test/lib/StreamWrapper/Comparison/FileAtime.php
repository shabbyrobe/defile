<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait FileAtime
{
    /**
     * @group fileatime
     * @group filesystemfunc
     */
    function testFileAtime()
    {
        $start = time();
        $this->put('/test', 'abcd');
        $time = fileatime("{$this->basePath}/test");
        $this->assertTrue(abs($time - $start) <= 1, "fileatime was $time, expected approx $start");
    }

    /**
     * @group fileatime
     * @group filesystemfunc
     */
    function testFileAtimeDir()
    {
        $start = time();
        $this->fileSystem->mkdir('/test');
        $time = fileatime("{$this->basePath}/test");
        $this->assertWithin(1, $time, $start);
    }

    /**
     * @group fileatime
     * @group filesystemfunc
     */
    function testFileAtimeNonexistent()
    {
        $result = fileatime("{$this->basePath}/test");
        $this->assertWarning("fileatime(): stat failed for {$this->basePath}/test");
        $this->assertFalse($result);
    }

    /**
     * @group fileatime
     * @group filesystemfunc
     */
    function testFileAtimeAfterTouch()
    {
        $this->put('/test', 'abcd');
        // mtime of 9999 is a canary
        touch("{$this->basePath}/test", 9999, 1234);
        $time = fileatime("{$this->basePath}/test");
        $this->assertEquals(1234, $time);
    }

    /**
     * @group fileatime
     * @group filesystemfunc
     */
    function testFileAtimeAfterWrite()
    {
        $this->fileSystem->touch("/test", 9999, 1234);
        clearstatcache();
        // sanity check
        $this->assertEquals(1234, fileatime("{$this->basePath}/test"));

        file_put_contents("{$this->basePath}/test", "efgh");
        clearstatcache();

        $start = time();
        $time = fileatime("{$this->basePath}/test");
        $this->assertEquals(1234, $time);
    }

    /**
     * @group fileatime
     * @group filesystemfunc
     */
    function testFileAtimeAfterRead()
    {
        $this->fileSystem->touch("/test", 9999, 1234);
        clearstatcache();
        // sanity check
        $this->assertEquals(1234, fileatime("{$this->basePath}/test"));

        file_get_contents("{$this->basePath}/test");
        clearstatcache();

        $start = time();
        $time = fileatime("{$this->basePath}/test");
        $this->assertTrue(abs($time - $start) <= 1, "fileatime was $time, expected approx $start");
    }
}

