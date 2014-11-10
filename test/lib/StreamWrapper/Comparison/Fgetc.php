<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait Fgetc
{
    /**
     * @group fgetc
     * @group filesystemfunc
     */
    function testFgetcRead()
    {
        $this->put("/foo", "abcd");
        $h = fopen("{$this->basePath}/foo", "r");
        $this->assertEquals('a', fgetc($h));
        $this->assertEquals('b', fgetc($h));
        $this->assertEquals('c', fgetc($h));
        $this->assertEquals('d', fgetc($h));
        $this->assertFalse(fgetc($h));
        $this->assertFalse(fgetc($h));
    }

    /**
     * @group fgetc
     * @group filesystemfunc
     */
    function testFgetcSeek()
    {
        $this->put("/foo", "abcd");
        $h = fopen("{$this->basePath}/foo", "r");
        $this->assertEquals('a', fgetc($h));
        fseek($h, 0);
        $this->assertEquals('a', fgetc($h));
        fseek($h, 5);
        $this->assertFalse(fgetc($h));
    }

    /**
     * @group fgetc
     * @group filesystemfunc
     */
    function testFgetcAfterClose()
    {
        $this->put("/foo", "abcd");
        $h = fopen("{$this->basePath}/foo", "r");
        fclose($h);

        $result = fgetc($h);
        $this->assertWarningRegexp("/^fgetc\(\): \d+ is not a valid stream resource$/");
        $this->assertFalse($result);
    }
}
