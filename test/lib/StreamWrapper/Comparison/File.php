<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait File
{
    /**
     * @group file
     * @group filesystemfunc
     */
    function testFilePlatformSpecificNoAutodetect()
    {
        $nl = PHP_EOL;
        $this->put("/foo", "line1{$nl}line2{$nl}{$nl}line3");
        $contents = file("{$this->basePath}/foo");
        $expected = ["line1{$nl}", "line2{$nl}", "{$nl}", "line3"];
        $this->assertEquals($expected, $contents);
    }

    /**
     * @group file
     * @group filesystemfunc
     */
    function testFileNonexistentFails()
    {
        $result = file("{$this->basePath}/nope");
        $this->assertWarning($this->tpl('openFails', ['file'=>'/nope', 'func'=>'file']));
        $this->assertFalse($result);
    }

    /**
     * @group file
     * @group filesystemfunc
     */
    function testFileWithDir()
    {
        $this->fileSystem->mkdir('/foo');
        $out = file("{$this->basePath}/foo");
        $this->assertEquals([], $out);
    }

    /**
     * @group file
     * @group filesystemfunc
     */
    function testFileWithCrNoAutodetect()
    {
        $in = "line1\rline2\r\rline3";
        $this->put("/foo", $in);
        $contents = file("{$this->basePath}/foo");
        $expected = [$in];
        $this->assertEquals($expected, $contents);
    }

    /**
     * @group file
     * @group filesystemfunc
     */
    function testFileWithCrAutodetect()
    {
        ini_set('auto_detect_line_endings', 1);
        $in = "line1\rline2\r\rline3";
        $this->put("/foo", $in);
        $contents = file("{$this->basePath}/foo");
        $expected = ["line1\r", "line2\r", "\r", "line3"];
        $this->assertEquals($expected, $contents);
    }
}
