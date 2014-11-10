<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait Fwrite
{
    function testFwriteReadModeReturnsZero()
    {
        $this->put("/foo", "abcd");
        $h = fopen("{$this->basePath}/foo", "r");
        $result = fwrite($h, 'test');
        $this->assertEquals(0, $result);
    }

    function testFwriteReadPlusMode()
    {
        $this->put("/foo", "abcd");
        $h = fopen("{$this->basePath}/foo", "r+");
        $result = fwrite($h, 'test');
        $this->assertEquals(4, $result);
        $this->assertFileContains('/foo', 'test');
    }

    /**
     * Ensures that the existing file is fully truncated - no hanging
     * content at the end
     */
    function testFwriteWriteModeExistingFileWithLessContent()
    {
        $this->put("/foo", "abcdefgh");
        $h = fopen("{$this->basePath}/foo", "w");
        $result = fwrite($h, 'test');
        $this->assertEquals(4, $result);
        $this->assertFileContains('/foo', 'test');
    }

    function testFwriteWriteModeExistingFileWithMoreContent()
    {
        $this->put("/foo", "abcd");
        $h = fopen("{$this->basePath}/foo", "w");
        $result = fwrite($h, 'testtest');
        $this->assertEquals(8, $result);
        $this->assertFileContains('/foo', 'testtest');
    }

    function testFwriteAppendModeExisting()
    {
        $this->put("/foo", "abcd");
        $h = fopen("{$this->basePath}/foo", "a");
        $result = fwrite($h, 'test');
        $this->assertEquals(4, $result);
        $this->assertFileContains('/foo', 'abcdtest');
    }

    function testFwriteAppendPlusModeExisting()
    {
        $this->put("/foo", "abcd");
        $h = fopen("{$this->basePath}/foo", "a+");
        $result = fwrite($h, 'test');
        $this->assertEquals(4, $result);
        $this->assertFileContains('/foo', 'abcdtest');
    }

    private function doTestWritableModeNonexistentWorking($mode)
    {
        $h = fopen("{$this->basePath}/foo", $mode);
        $result = fwrite($h, 'testtest');
        $this->assertEquals(8, $result);
        $this->assertFileContains('/foo', 'testtest');
    }

    function testFwriteWModeNonexistent()      { $this->doTestWritableModeNonexistentWorking("w"); }
    function testFwriteWPlusModeNonexistent()  { $this->doTestWritableModeNonexistentWorking("w+"); }
    function testFwriteWBPlusModeNonexistent() { $this->doTestWritableModeNonexistentWorking("wb+"); }
    function testFwriteAModeNonexistent()      { $this->doTestWritableModeNonexistentWorking("a"); }
    function testFwriteAPlusModeNonexistent()  { $this->doTestWritableModeNonexistentWorking("a+"); }
    function testFwriteABPlusModeNonexistent() { $this->doTestWritableModeNonexistentWorking("ab+"); }
    function testFwriteCModeNonexistent()      { $this->doTestWritableModeNonexistentWorking("c"); }
    function testFwriteCPlusModeNonexistent()  { $this->doTestWritableModeNonexistentWorking("c+"); }
    function testFwriteCBPlusModeNonexistent() { $this->doTestWritableModeNonexistentWorking("cb+"); }
}
