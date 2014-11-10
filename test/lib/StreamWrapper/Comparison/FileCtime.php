<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait FileCtime
{
    /**
     * @group filectime
     * @group filesystemfunc
     */
    function testFileCtime()
    {
        $start = time();
        $this->put('/test', 'abcd');
        clearstatcache();

        $time = filectime("{$this->basePath}/test");
        $this->assertTrue(abs($time - $start) <= 1, "filectime was $time, expected approx $start");
    }

    /**
     * @group filectime
     * @group filesystemfunc
     */
    function testFileCtimeDir()
    {
        $start = time();
        $this->fileSystem->mkdir('/test');
        clearstatcache();

        $time = filectime("{$this->basePath}/test");
        $this->assertTrue(abs($time - $start) <= 1, "filectime was $time, expected approx $start");
    }

    /**
     * @group filectime
     * @group filesystemfunc
     */
    function testFileCtimeAfterTouchMtime()
    {
        $start = time();
        $this->put('/test', 'abcd');
        touch("{$this->basePath}/test", 1234);
        clearstatcache();

        $time = filectime("{$this->basePath}/test");
        $this->assertTrue(abs($time - $start) <= 1, "filectime was $time, expected approx $start");
    }

    /**
     * @group filectime
     * @group filesystemfunc
     */
    function testFileCtimeAfterTouchAtime()
    {
        $start = time();
        $this->put('/test', 'abcd');
        touch("{$this->basePath}/test", 9999, 1234);
        clearstatcache();

        $time = filectime("{$this->basePath}/test");
        $this->assertTrue(abs($time - $start) <= 1, "filectime was $time, expected approx $start");
    }

    /**
     * @group filectime
     * @group filesystemfunc
     */
    function testFileCtimeAfterWrite()
    {
        $start = time();
        $this->put('/test', 'abcd');
        file_put_contents("{$this->basePath}/test", "efgh");
        $time = filectime("{$this->basePath}/test");
        $this->assertTrue(abs($time - $start) <= 1, "filectime was $time, expected approx $start");
    }
}
