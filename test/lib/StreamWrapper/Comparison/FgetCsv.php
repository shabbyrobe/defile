<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait FgetCsv
{
    /**
     * @group fgetcsv
     * @group filesystemfunc
     */
    function testFgetCsv()
    {
        $this->put('/foo', implode("\n", [
            "foo,bar,baz",
            "1,2,3",
            "4,5,6"
        ]));
        $h = fopen("{$this->basePath}/foo", "r");
        $this->assertEquals(['foo', 'bar', 'baz'], fgetcsv($h));
        $this->assertEquals(['1', '2', '3'], fgetcsv($h));
        $this->assertEquals(['4', '5',' 6'], fgetcsv($h));
        $this->assertEquals(false, fgetcsv($h));
    }

    /**
     * @group fgetcsv
     * @group filesystemfunc
     */
    function testFgetCsvWithClosedHandleFails()
    {
        $this->put('/foo', implode("\n", [
            "foo,bar,baz",
            "1,2,3",
            "4,5,6"
        ]));
        $h = fopen("{$this->basePath}/foo", "r");
        fclose($h);
        $result = fgetcsv($h);
        $this->assertWarningRegexp("/fgetcsv\(\): \d+ is not a valid stream resource/");
        $this->assertFalse($result);
    }
}
