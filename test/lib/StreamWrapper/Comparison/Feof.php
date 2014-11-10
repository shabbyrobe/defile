<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait Feof
{
    /**
     * @group feof
     * @group filesystemfunc
     */
    function testFeofReadWithoutStreamChunking()
    {
        $fullFile = "{$this->basePath}/test";
        $this->put('/test', 'abcd');
        $h = fopen($fullFile, "r");

        // See stream_chunk_ensure_quirk
        stream_set_chunk_size($h, 1);

        $this->assertFalse(feof($h));
        fread($h, 2);
        $this->assertFalse(feof($h));
        fread($h, 2);
        $this->assertFalse(feof($h));
        fread($h, 1);
        $this->assertTrue(feof($h));
        fseek($h, 2);
        $this->assertFalse(feof($h));
    }
}
