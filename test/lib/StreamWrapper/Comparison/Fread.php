<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait Fread
{
    /**
     * @group fread
     * @group acceptance
     */
    function testFreadReadModeFirstPortionLessThanDefaultStreamChunk()
    {
        $this->put("/foo", $this->createBuffer(8*3));
        $h = fopen("{$this->basePath}/foo", "r");
        $result = fread($h, 8);
        $this->assertEquals("0000001\n", $result);
    }

    /**
     * @group fread
     * @group acceptance
     */
    function testFreadCPlusModeFirstPortionLessThanDefaultStreamChunk()
    {
        $this->put("/foo", $this->createBuffer(8*3));
        $h = fopen("{$this->basePath}/foo", "c+");
        $result = fread($h, 8);
        $this->assertEquals("0000001\n", $result);
    }

    /**
     * @group fread
     * @group acceptance
     */
    function testFreadXPlusModeFirstPortionLessThanDefaultStreamChunk()
    {
        $h = fopen("{$this->basePath}/foo", "x+");
        fwrite($h, $this->createBuffer(8*3));
        rewind($h);
        $result = fread($h, 8);
        $this->assertEquals("0000001\n", $result);
    }

    /**
     * @group fread
     * @group acceptance
     */
    function testFreadReadModeFirstPortionEqualToDefaultStreamChunk()
    {
        $buf = $this->createBuffer(8192 * 3);
        $this->put("/foo", $buf);
        $h = fopen("{$this->basePath}/foo", "r");
        $result = fread($h, 8192);
        $this->assertEquals(substr($buf, 0, 8192), $result);
    }

    /**
     * @group fread
     * @group acceptance
     */
    function testFreadReadModeFirstPortionGreaterThanDefaultStreamChunk()
    {
        $buf = $this->createBuffer(8192 * 3);
        $this->put("/foo", $buf);
        $h = fopen("{$this->basePath}/foo", "r");
        $result = fread($h, 8193);
        $this->assertEquals(substr($buf, 0, 8193), $result);
    }

    /**
     * @group fread
     * @group acceptance
     */
    function testFreadReadModeMiddlePortionLessThanDefaultStreamChunk()
    {
        $this->put("/foo", "abcdefgh");
        $h = fopen("{$this->basePath}/foo", "r");
        fseek($h, 2);
        $result = fread($h, 4);
        $this->assertEquals('cdef', $result);
    }

    /**
     * @group fread
     * @group acceptance
     */
    function testFreadReadModeLastPortionLessThanDefaultStreamChunk()
    {
        $this->put("/foo", "abcdefgh");
        $h = fopen("{$this->basePath}/foo", "r");
        fseek($h, 4);
        $result = fread($h, 4);
        $this->assertEquals('efgh', $result);
    }

    /**
     * @group fread
     * @group acceptance
     */
    function testFreadReadModePortionOverEndLessThanDefaultStreamChunk()
    {
        $this->put("/foo", "abcdefgh");
        $h = fopen("{$this->basePath}/foo", "r");
        fseek($h, 6);
        $result = fread($h, 4);
        $this->assertEquals('gh', $result);
    }

    /**
     * @group fread
     * @group acceptance
     */
    function testFreadReadModeMiddlePortionEqualToDefaultStreamChunk()
    {
        $buf = $this->createBuffer(8192 * 3);
        $this->put("/foo", $buf);
        $h = fopen("{$this->basePath}/foo", "r");
        fseek($h, 8192);
        $result = fread($h, 8192);
        $this->assertEquals(substr($buf, 8192, 8192), $result);
    }

    /**
     * @group fread
     * @group acceptance
     */
    function testFreadReadModeMiddlePortionGreaterThanDefaultStreamChunk()
    {
        $buf = $this->createBuffer(8192 * 3);
        $this->put("/foo", $buf);
        $h = fopen("{$this->basePath}/foo", "r");
        fseek($h, 8192);
        $result = fread($h, 8193);
        $this->assertEquals(substr($buf, 8192, 8193), $result);
    }

    /**
     * @group fread
     * @group acceptance
     */
    function testFreadReadModeLastPortionEqualToDefaultStreamChunk()
    {
        $buf = $this->createBuffer(8192 * 3);
        $this->put("/foo", $buf);
        $h = fopen("{$this->basePath}/foo", "r");
        fseek($h, 16384);
        $result = fread($h, 8192);
        $this->assertEquals(substr($buf, 16384, 8192), $result);
    }

    /**
     * @group fread
     * @group acceptance
     */
    function testFreadWriteMode()
    {
        $this->put("/foo", "abcd");
        $h = fopen("{$this->basePath}/foo", "w");
        $result = fread($h, 100);
        $this->assertEquals('', $result);
    }

    /**
     * @group fread
     * @group acceptance
     */
    function testFreadWritePlusMode()
    {
        // abcd is a canary
        $this->put("/foo", "abcd");

        $h = fopen("{$this->basePath}/foo", "w+");
        $result = fread($h, 100);
        $this->assertEquals('', $result);

        fwrite($h, 'efgh');

        $result = fread($h, 100);
        $this->assertEquals('', $result);

        rewind($h);
        $result = fread($h, 100);
        $this->assertEquals('efgh', $result);
    }

    /**
     * @group fread
     * @group acceptance
     */
    function testFreadAppendModeExisting()
    {
        $this->put("/foo", "abcd");

        $h = fopen("{$this->basePath}/foo", "a");
        $result = fread($h, 100);
        $this->assertEquals('', $result);

        fwrite($h, 'efgh');

        $result = fread($h, 100);
        $this->assertEquals('', $result);

        // FTM: seeking in append mode is undefined, though this is consistently
        // the behaviour on the platforms I've tested
        rewind($h);
        $result = fread($h, 100);
        $this->assertEquals('', $result);
    }

    /**
     * @group fread
     * @group acceptance
     */
    function testFreadAppendModeNew()
    {
        $h = fopen("{$this->basePath}/foo", "a");
        $result = fread($h, 100);
        $this->assertEquals('', $result);

        fwrite($h, 'efgh');

        $result = fread($h, 100);
        $this->assertEquals('', $result);

        // FTM: seeking in append mode is undefined, though this is consistently
        // the behaviour on the platforms I've tested
        rewind($h);
        $result = fread($h, 100);
        $this->assertEquals('', $result);
    }
}
