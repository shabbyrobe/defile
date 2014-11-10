<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait Chmod
{
    /**
     * @group chmod
     * @group filesystemfuncfunc
     */
    function testChmod()
    {
        umask(0);

        $this->put('/test', '1234');
        $fullFile = "{$this->basePath}/test";
        
        // sanity check
        $stat = stat($fullFile);
        $mode = $stat['mode'] & 0777;
        $this->assertEquals(0666, $mode);

        $result = chmod($fullFile, 0644);
        $this->assertTrue($result);
        clearstatcache();

        $stat = stat($fullFile);
        $mode = $stat['mode'] & 0777;
        $this->assertEquals(0644, $mode);

        $result = chmod($fullFile, 0755);
        $this->assertTrue($result);
        clearstatcache();

        $stat = stat($fullFile);
        $mode = $stat['mode'] & 0777;
        $this->assertEquals(0755, $mode);
    }

    /**
     * @group chmod
     * @group filesystemfunc
     */
    function testChmodNonexistentFails()
    {
        $result = chmod("{$this->basePath}/foo", 0775);
        $this->assertWarning("chmod(): No such file or directory");
        $this->assertFalse($result);
    }

    /**
     * @group chmod
     * @group filesystemfunc
     */
    function testChmodWtf()
    {
        $this->put('/test', '1234');
        $fullFile = "{$this->basePath}/test";
        
        // sanity check
        $stat = stat($fullFile);
        $mode = $stat['mode'] & 0777;
        $this->assertEquals(0666, $mode);

        // sort of guessing what happens here.
        $result = chmod($fullFile, 918290312);
        $this->assertTrue($result);
        clearstatcache();

        $stat = stat($fullFile);
        $mode = $stat['mode'] & 07777;
        $this->assertEquals(07610, $mode);
    }
}
