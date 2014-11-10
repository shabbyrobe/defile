<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait Ftruncate
{
    /**
     * @group ftruncate
     * @group filesystemfunc
     */
    function testFtruncateClosedHandleFails()
    {
        $this->put('/test', 'abcd');
        $h = fopen("{$this->basePath}/test", "r");
        fclose($h);
        $result = ftruncate($h, 0);
        $this->assertWarningRegexp("/ftruncate\(\): \d+ is not a valid stream resource/");
        $this->assertFalse($result);
    }

    /**
     * @group ftruncate
     * @group filesystemfunc
     */
    function testFtruncateReadNegativeFails()
    {
        $this->put('/test', 'abcd');
        $h = fopen("{$this->basePath}/test", "r");
        $result = ftruncate($h, -10);
        $this->assertFalse($result);
        fclose($h);

        $this->assertEquals('abcd', file_get_contents("{$this->basePath}/test"));
    }

    /**
     * @group ftruncate
     * @group filesystemfunc
     */
    function testFtruncateReadReturnsFalse()
    {
        $this->put('/test', 'abcd');
        $h = fopen("{$this->basePath}/test", "r");
        $result = ftruncate($h, 0);
        $this->assertFalse($result);
    }

    /**
     * @group ftruncate
     * @group filesystemfunc
     */
    function testFtruncateReadPlus()
    {
        $this->put('/test', 'abcd');
        $h = fopen("{$this->basePath}/test", "r+");
        $result = ftruncate($h, 0);
        $this->assertTrue($result);
        fseek($h, 0, SEEK_END);
        $this->assertEquals(0, ftell($h));
        fclose($h);

        $this->assertEquals(0, strlen(file_get_contents("{$this->basePath}/test")));
    }

    /**
     * @group ftruncate
     * @group filesystemfunc
     */
    function testFtruncateReadPlusExtend()
    {
        $this->put('/test', '0123456789');
        $h = fopen("{$this->basePath}/test", "r+");
        $result = ftruncate($h, 15);
        $this->assertTrue($result);
        fseek($h, 0, SEEK_END);
        $this->assertEquals(15, ftell($h));
        $this->assertEquals("0123456789\x00\x00\x00\x00\x00", file_get_contents("{$this->basePath}/test"));
    }

    /**
     * @group ftruncate
     * @group filesystemfunc
     */
    function testFtruncateReadPlusDoesntAlterPosition()
    {
        $this->put('/test', '0123456789');
        $h = fopen("{$this->basePath}/test", "r+");
        fseek($h, 4);
        $result = ftruncate($h, 0);
        $this->assertEquals(4, ftell($h));
    }

    /**
     * @group ftruncate
     * @group filesystemfunc
     */
    function testFtruncateWrite()
    {
        $this->put('/test', 'abcd');
        $h = fopen("{$this->basePath}/test", "w");
        $result = ftruncate($h, 0);
        fclose($h);
        $this->assertEquals(0, strlen(file_get_contents("{$this->basePath}/test")));
    }

    /**
     * @group ftruncate
     * @group filesystemfunc
     */
    function testFtruncateWriteExtend()
    {
        $this->put('/test', '0123456789');
        $h = fopen("{$this->basePath}/test", "w");
        $result = ftruncate($h, 5);
        $this->assertTrue($result);
        fseek($h, 0, SEEK_END);
        $this->assertEquals(5, ftell($h));
        $this->assertEquals("\x00\x00\x00\x00\x00", file_get_contents("{$this->basePath}/test"));
    }

    /**
     * @group ftruncate
     * @group filesystemfunc
     */
    function testFtruncateWriteDoesntAlterPosition()
    {
        $this->put('/test', '0123456789');
        $h = fopen("{$this->basePath}/test", "w");
        fseek($h, 4);
        $result = ftruncate($h, 0);
        $this->assertEquals(4, ftell($h));
    }

    /**
     * @group ftruncate
     * @group filesystemfunc
     */
    function testFtruncateWritePlus()
    {
        $this->put('/test', 'abcd');
        $h = fopen("{$this->basePath}/test", "w+");
        $result = ftruncate($h, 0);
        $this->assertTrue($result);
        fseek($h, 0, SEEK_END);
        $this->assertEquals(0, ftell($h));
        fclose($h);

        $this->assertEquals(0, strlen(file_get_contents("{$this->basePath}/test")));
    }

    /**
     * @group ftruncate
     * @group filesystemfunc
     */
    function testFtruncateWritePlusExtend()
    {
        $this->put('/test', '0123456789');
        $h = fopen("{$this->basePath}/test", "w+");
        $result = ftruncate($h, 5);
        $this->assertTrue($result);
        fseek($h, 0, SEEK_END);
        $this->assertEquals(5, ftell($h));
        $this->assertEquals("\x00\x00\x00\x00\x00", file_get_contents("{$this->basePath}/test"));
    }

    /**
     * @group ftruncate
     * @group filesystemfunc
     */
    function testFtruncateWritePlusDoesntAlterPosition()
    {
        $this->put('/test', '0123456789');
        $h = fopen("{$this->basePath}/test", "w+");
        fseek($h, 4);
        $result = ftruncate($h, 0);
        $this->assertEquals(4, ftell($h));
    }
}
