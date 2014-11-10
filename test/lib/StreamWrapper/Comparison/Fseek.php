<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait Fseek
{
    /**
     * @group fseek
     * @group filesystemfunc
     */
    function testFseekSet()
    {
        $this->put('/test', '0123456789');
        $h = fopen("{$this->basePath}/test", 'r');
        $result = fseek($h, 5, SEEK_SET);
        $this->assertEquals(0, $result);
        $this->assertEquals(5, ftell($h));
    }

    /**
     * @group fseek
     * @group filesystemfunc
     */
    function testFseekCurNeg()
    {
        $this->put('/test', '0123456789');
        $h = fopen("{$this->basePath}/test", 'r');

        $result = fseek($h, 4, SEEK_SET);
        $this->assertEquals(0, $result);

        $result = fseek($h, -2, SEEK_CUR);
        $this->assertEquals(0, $result);

        $this->assertEquals(2, ftell($h));
    }

    /**
     * @group fseek
     * @group filesystemfunc
     */
    function testFseekCurNegPastStart()
    {
        $this->put('/test', '0123456789');
        $h = fopen("{$this->basePath}/test", 'r');

        $result = fseek($h, 3, SEEK_SET);
        $this->assertEquals(0, $result);

        $result = fseek($h, -8, SEEK_CUR);
        $this->assertEquals(-1, $result);

        $this->assertEquals(3, ftell($h));
    }

    /**
     * @group fseek
     * @group filesystemfunc
     */
    function testFseekCurPos()
    {
        $this->put('/test', '0123456789');
        $h = fopen("{$this->basePath}/test", 'r');
        
        $result = fseek($h, 3, SEEK_SET);
        $this->assertEquals(0, $result);

        $result = fseek($h, 5, SEEK_CUR);
        $this->assertEquals(0, $result);

        $this->assertEquals(8, ftell($h));
    }

    /**
     * @group fseek
     * @group filesystemfunc
     */
    function testFseekEndNeg()
    {
        $this->put('/test', '0123456789');
        $h = fopen("{$this->basePath}/test", 'r');

        $result = fseek($h, -3, SEEK_END);
        $this->assertEquals(0, $result);

        $this->assertEquals(7, ftell($h));
    }

    /**
     * @group fseek
     * @group filesystemfunc
     */
    function testFseekEndPos()
    {
        $this->put('/test', '0123456789');
        $h = fopen("{$this->basePath}/test", 'r');

        $result = fseek($h, 3, SEEK_END);
        $this->assertEquals(0, $result);

        $this->assertEquals(13, ftell($h));
    }

    /**
     * @group fseek
     * @group filesystemfunc
     */
    function testFseekSetBeforeEndReadPastEnd()
    {
        $this->put('/test', '0123456789');
        $h = fopen("{$this->basePath}/test", 'r');

        $result = fseek($h, 5, SEEK_SET);
        $this->assertEquals(0, $result);

        $buf = fread($h, 100);
        $this->assertEquals('56789', $buf);
    }

    /**
     * @group fseek
     * @group filesystemfunc
     */
    function testFseekEndReadBeforeEnd()
    {
        $this->put('/test', '0123456789');
        $h = fopen("{$this->basePath}/test", 'r');

        $result = fseek($h, -3, SEEK_END);
        $this->assertEquals(0, $result);

        $buf = fread($h, 100);
        $this->assertEquals('789', $buf);
    }

    /**
     * @group fseek
     * @group filesystemfunc
     */
    function testFseekEndReadAtEnd()
    {
        $this->put('/test', '0123456789');
        $h = fopen("{$this->basePath}/test", 'r');

        $result = fseek($h, 0, SEEK_END);
        $this->assertEquals(0, $result);

        $buf = fread($h, 100);
        $this->assertEquals('', $buf);
    }

    /**
     * @group fseek
     * @group filesystemfunc
     */
    function testFseekEndReadPastEnd()
    {
        $this->put('/test', '0123456789');
        $h = fopen("{$this->basePath}/test", 'r');

        $result = fseek($h, 3, SEEK_END);
        $this->assertEquals(0, $result);

        $buf = fread($h, 100);
        $this->assertEquals('', $buf);
    }

    /**
     * @group fseek
     * @group filesystemfunc
     */
    function testFseekReadWriteAfterEnd()
    {
        $file = "/".__FUNCTION__;
        $this->put($file, '012345');
        $h = fopen("{$this->basePath}/$file", 'r+');

        $result = fseek($h, 4, SEEK_END);
        $this->assertEquals(0, $result);

        fwrite($h, '67890');
        $this->assertFileContains($file, "012345\x00\x00\x00\x0067890");
    }

    /**
     * @group fseek
     * @group filesystemfunc
     */
    function testFseekStartReadWriteAfterEnd()
    {
        $file = "/".__FUNCTION__;
        $this->put($file, '012345');
        $h = fopen("{$this->basePath}/$file", 'r+');

        $result = fseek($h, 10, SEEK_SET);
        $this->assertEquals(0, $result);

        fwrite($h, '67890');
        $this->assertFileContains($file, "012345\x00\x00\x00\x0067890");
    }

    /**
     * @group fseek
     * @group filesystemfunc
     */
    function testFseekCurReadWriteAfterEnd()
    {
        $file = "/".__FUNCTION__;
        $this->put($file, '012345');
        $h = fopen("{$this->basePath}/$file", 'r+');
       
        $result = fseek($h, 5);
        $this->assertEquals(0, $result);

        $result = fseek($h, 5, SEEK_CUR);
        $this->assertEquals(0, $result);

        fwrite($h, '67890');
        $this->assertFileContains($file, "012345\x00\x00\x00\x0067890");
    }
}
