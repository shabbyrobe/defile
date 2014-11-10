<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait Fstat
{
    /**
     * @group fstat
     * @group filesystemfunc
     */
    function testFstat()
    {
        $this->put('/foo', 'abcd');
        $h = fopen("{$this->basePath}/foo", "r");
        $stat = fstat($h);
        $this->assertInternalType('array', $stat);

        $this->assertEquals(0100666, $stat['mode']);
        $this->assertEquals(0100666, $stat[2]);

        $this->assertEquals(4, $stat['size']);
        $this->assertEquals(4, $stat[7]);
    }

    /**
     * @group fstat
     * @group filesystemfunc
     */
    function testFstatAfterDeleted()
    {
        $this->put('/foo', 'abcd');
        $h = fopen("{$this->basePath}/foo", "r");
        $this->fileSystem->rm('/foo');
        $this->assertNotExists('/foo');

        // still exists while an open file handle remains
        $stat = fstat($h);
        $this->assertInternalType('array', $stat);

        $this->assertEquals(0100666, $stat['mode']);
        $this->assertEquals(0100666, $stat[2]);

        $this->assertEquals(4, $stat['size']);
        $this->assertEquals(4, $stat[7]);
    }
}
