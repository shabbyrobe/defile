<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait Ftell
{
    /**
     * @group ftell
     * @group filesystemfunc
     */
    function testFtell()
    {
        $this->put('/test', '1234');
        $h = fopen("{$this->basePath}/test", "r");
        fread($h, 2);
        $this->assertEquals(2, ftell($h));
    }

    /**
     * @group ftell
     * @group filesystemfunc
     */
    function testFtellClosedFails()
    {
        $this->put('/test', '1234');
        $h = fopen("{$this->basePath}/test", "r");
        fread($h, 2);
        fclose($h);
        $result = ftell($h);

        $this->assertWarningRegexp("/ftell\(\): \d+ is not a valid stream resource/");
        $this->assertFalse($result);
    }
}
