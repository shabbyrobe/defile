<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait Fpassthru
{
    /**
     * @group fpassthru
     * @group filesystemfunc
     */
    function testFpassthruRead()
    {
        $this->put('/foo', 'abcd');
        $h = fopen("{$this->basePath}/foo", "r");
        ob_start();
        $bytes = fpassthru($h);
        $out = ob_get_clean();
        $this->assertEquals(4, $bytes);
        $this->assertEquals('abcd', $out);
    }

    /**
     * @group fpassthru
     * @group filesystemfunc
     */
    function testFpassthruReadPlus()
    {
        $this->put('/foo', 'abcd');
        $h = fopen("{$this->basePath}/foo", "r+");
        ob_start();
        $bytes = fpassthru($h);
        $out = ob_get_clean();
        $this->assertEquals(4, $bytes);
        $this->assertEquals('abcd', $out);
    }

    /**
     * @group fpassthru
     * @group filesystemfunc
     */
    function testFpassthruC()
    {
        $this->put('/foo', 'abcd');
        $h = fopen("{$this->basePath}/foo", "c");
        ob_start();
        try {
            $bytes = fpassthru($h);
        }
        finally {
            $out = ob_get_clean();
        }
        $this->assertEquals('', $out);
        $this->assertEquals(0, $bytes);
    }
}
