<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait Rewind
{
    /**
     * @group rewind
     * @group filesystemfunc
     */
    function testRewindRead()
    {
        $this->put('/foo', '0123456789');
        $h = fopen("{$this->basePath}/foo", "r");
        fseek($h, 10);
        $this->assertEquals(10, ftell($h));

        $result = rewind($h);
        $this->assertTrue($result);
        $this->assertEquals(0, ftell($h));
    }

    /**
     * @group rewind
     * @group filesystemfunc
     */
    function testRewindReadPlus()
    {
        $this->put('/foo', '0123456789');
        $h = fopen("{$this->basePath}/foo", "r+");
        fseek($h, 10);
        $this->assertEquals(10, ftell($h));

        $result = rewind($h);
        $this->assertTrue($result);
        $this->assertEquals(0, ftell($h));
    }

    /**
     * @group rewind
     * @group filesystemfunc
     */
    function testRewindWriteNew()
    {
        $h = fopen("{$this->basePath}/foo", "w");
        fseek($h, 10);
        $this->assertEquals(10, ftell($h));

        $result = rewind($h);
        $this->assertTrue($result);
        $this->assertEquals(0, ftell($h));
    }

    /**
     * @group rewind
     * @group filesystemfunc
     */
    function testRewindWritePlusNew()
    {
        $h = fopen("{$this->basePath}/foo", "w+");
        fseek($h, 10);
        $this->assertEquals(10, ftell($h));

        $result = rewind($h);
        $this->assertTrue($result);
        $this->assertEquals(0, ftell($h));
    }

    /**
     * TFM: If you have opened the file in append ("a" or "a+") mode, 
     * any data you write to the file will always be appended, regardless
     * of the file position.
     * @group rewind
     * @group filesystemfunc
     */
    function testRewindAppend()
    {
        $this->put('/foo', '01234567890');
        $h = fopen("{$this->basePath}/foo", "a");
        fwrite($h, 999);

        $result = rewind($h);
        $this->assertTrue($result);

        fwrite($h, 888);
        fclose($h);
        $this->assertFileContains('/foo', '01234567890999888');
    }

    /**
     * The file pointer does some weird things in append mode... the results
     * of ftell almost don't make much sense. TFM for ftell confirms it - ftell
     * gives undefined results for append streams. Leave the ftell checks in here
     * until we see inconsistencies, then remove them. It could mean 'undefined'
     * simply means 'consistent, but nonsensical'. Let's leave it in as a canary.
     * @group rewind
     * @group filesystemfunc
     */
    function testRewindAppendPlus()
    {
        $this->put('/foo', '01234567890');
        $h = fopen("{$this->basePath}/foo", "a+");
        $this->assertEquals(0, ftell($h));

        fwrite($h, 999);
        $this->assertEquals(3, ftell($h));

        $result = rewind($h);
        $this->assertTrue($result);
        $this->assertEquals(0, ftell($h));

        fwrite($h, 888);
        $this->assertEquals(3, ftell($h));

        $result = rewind($h);
        $this->assertTrue($result);
        $this->assertEquals('01234567890999888', fread($h, 100));

        fclose($h);
        $this->assertFileContains('/foo', '01234567890999888');
    }

    /**
     * @group rewind
     * @group filesystemfunc
     */
    function testRewindClosedHandle()
    {
        $this->put('/foo', '01234567890');
        $h = fopen("{$this->basePath}/foo", "r");
        fclose($h);
        $result = rewind($h);
        $this->assertWarningRegexp("/rewind\(\): \d+ is not a valid stream resource/");
        $this->assertFalse($result);
    }
}
