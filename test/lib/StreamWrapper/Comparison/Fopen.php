<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait Fopen
{
    /**
     * @group fopen
     * @group filesystemfunc
     */
    function testFopenExistsRead()
    {
        $this->put('/test', '012345678901234567890123456789123456789');

        $h = fopen("{$this->basePath}/test", 'r');
        $this->assertInternalType('resource', $h);

        $buf = fread($h, 32);
        $this->assertEquals('01234567890123456789012345678912', $buf);
    }

    /**
     * From TFM: This function may also succeed when $filename is a directory.
     * @group fopen
     * @group filesystemfunc
     */
    function testFopenReadDir()
    {
        $this->fileSystem->mkdir("/dir");
        $h = fopen("{$this->basePath}/dir", "r");
        $this->assertInternalType('resource', $h);

        $out = fread($h, 1000);
        $this->assertEquals('', $out);
    }

    /**
     * @group fopen
     * @group filesystemfunc
     */
    function testFopenReadModeNonexistentFails()
    {
        $file = "/unknown";
        $h = fopen("{$this->basePath}$file", 'r');
        $this->assertWarning($this->tpl('openFails', ['func'=>'fopen', 'file'=>$file]));
        $this->assertFalse($h);
    }

    /**
     * @group fopen
     * @group filesystemfunc
     */
    function testFopenReadPlusModeNonexistentFails()
    {
        $file = "/unknown";
        $h = fopen("{$this->basePath}$file", 'r+');
        $this->assertWarning($this->tpl('openFails', ['func'=>'fopen', 'file'=>$file]));
        $this->assertFalse($h);
    }

    /**
     * @group fopen
     * @group filesystemfunc
     */
    function testFopenWriteMode()
    {
        $file = '/test';
        $fullFile = "{$this->basePath}{$file}";
        $h = fopen($fullFile, 'w');
        $this->assertInternalType('resource', $h);
        $this->assertEquals(0, ftell($h));

        fwrite($h, '1234');
        fclose($h);
        $this->assertFileContains($file, '1234');
    }

    /**
     * @group fopen
     * @group filesystemfunc
     */
    function testFopenWriteModeUpdatesMtime()
    {
        $start = time();
        $this->fileSystem->touch('/foo', 1234);
        $h = fopen("{$this->basePath}/foo", 'w'); 
        $mtime = fstat($h)['mtime'];
        $this->assertGreaterThanOrEqual($start, $mtime);
    }

    /**
     * @group fopen
     * @group filesystemfunc
     */
    function testFopenWritePlusMode()
    {
        $file = '/test';
        $fullFile = "{$this->basePath}{$file}";
        $h = fopen($fullFile, 'w+');
        $this->assertInternalType('resource', $h);
        $this->assertEquals(0, ftell($h));

        fwrite($h, '1234');
        fclose($h);
        $this->assertFileContains($file, '1234');
    }

    /**
     * @group fopen
     * @group filesystemfunc
     */
    function testFopenWritePlusModeUpdatesMtime()
    {
        $start = time();
        $this->fileSystem->touch('/foo', 1234);
        $h = fopen("{$this->basePath}/foo", 'w+'); 
        $mtime = fstat($h)['mtime'];
        $this->assertGreaterThanOrEqual($start, $mtime);
    }

    /**
     * @group fopen
     * @group filesystemfunc
     */
    function testFopenAppendModeNonexistent()
    {
        $file = '/test';
        $fullFile = "{$this->basePath}{$file}";
        $h = fopen($fullFile, 'a');
        $this->assertInternalType('resource', $h);
        $this->assertEquals(0, ftell($h));

        fwrite($h, '1234');
        $this->assertFileContains($file, '1234');
    }

    /**
     * @group fopen
     * @group filesystemfunc
     */
    function testFopenAppendModeExisting()
    {
        $file = '/test';
        $fullFile = "{$this->basePath}{$file}";

        $this->put($file, '1234');
        $h = fopen($fullFile, 'a');
        $this->assertInternalType('resource', $h);

        // ftell gives undefined results in append mode
        // TODO: this should be tested in the Stream tests to conform to
        // the same expected value, even if it's technically still 'undefined'
        // $this->assertEquals(4, ftell($h));

        fwrite($h, '5678');
        $this->assertFileContains($file, '12345678');
    }

    /**
     * @group fopen
     * @group filesystemfunc
     */
    function testFopenAppendModeExistingDoesntUpdateMtime()
    {
        $this->fileSystem->touch('/foo', 1234);
        $h = fopen("{$this->basePath}/foo", 'a'); 
        $mtime = fstat($h)['mtime'];
        $this->assertEquals(1234, $mtime);
    }

    /**
     * @group fopen
     * @group filesystemfunc
     */
    function testFopenCModeNonexistent()
    {
        $file = '/test';
        $fullFile = "{$this->basePath}{$file}";
        $h = fopen($fullFile, 'c');
        $this->assertInternalType('resource', $h);
        $this->assertEquals(0, ftell($h));

        fwrite($h, '1234');
        $this->assertFileContains($file, '1234');
    }

    /**
     * @group fopen
     * @group filesystemfunc
     */
    function testFopenCModeExisting()
    {
        $file = '/test';
        $fullFile = "{$this->basePath}{$file}";
        $this->put($file, '1234');
        $h = fopen($fullFile, 'c+');
        $this->assertInternalType('resource', $h);
        $this->assertEquals(0, ftell($h));

        fwrite($h, '5678');
        $this->assertFileContains($file, '5678');
    }

    /**
     * @group fopen
     * @group filesystemfunc
     */
    function testFopenCModeExistingDoesntUpdateMtime()
    {
        $this->fileSystem->touch('/foo', 1234);
        $h = fopen("{$this->basePath}/foo", 'c'); 
        $mtime = fstat($h)['mtime'];
        $this->assertEquals(1234, $mtime);
    }

    /**
     * @group fopen
     * @group filesystemfunc
     */
    function testFopenXmodeNonexistent()
    {
        $file = '/test';
        $fullFile = "{$this->basePath}{$file}";
        $h = fopen($fullFile, 'x');
        $this->assertInternalType('resource', $h);
        $this->assertEquals(0, ftell($h));

        fwrite($h, '1234');
        $this->assertFileContains($file, '1234');
    }

    /**
     * @group fopen
     * @group filesystemfunc
     */
    function testFopenXPlusModeNonexistent()
    {
        $file = '/test';
        $h = fopen("{$this->basePath}{$file}", 'x+');
        $this->assertInternalType('resource', $h);
        $this->assertEquals(0, ftell($h));

        fwrite($h, '5678');
        $this->assertFileContains($file, '5678');
    }

    /**
     * @group fopen
     * @group filesystemfunc
     */
    function testFopenXPlusModeExistingFails()
    {
        $this->put('/test', '1234');
        $file = '/test';
        $result = fopen("{$this->basePath}{$file}", 'x+');
        $this->assertWarning($this->tpl('openFails', ['func'=>'fopen', 'file'=>$file, 'msg'=>'File exists']));
        $this->assertFalse($result);
    }
}
