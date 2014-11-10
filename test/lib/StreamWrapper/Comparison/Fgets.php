<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait Fgets
{
    /**
     * @group fgets
     * @group filesystemfunc
     */
    function testFgets()
    {
        $nl = PHP_EOL;
        $this->put('/test', "foo{$nl}bar{$nl}baz");
        $h = fopen("{$this->basePath}/test", "r"); 
        $this->assertEquals("foo{$nl}", fgets($h));
        $this->assertEquals("bar{$nl}", fgets($h));
        $this->assertEquals("baz", fgets($h));
        $this->assertFalse(fgets($h));
    }

    /**
     * @group fgets
     * @group filesystemfunc
     */
    function testFgetsWithCrAutodetect()
    {
        ini_set('auto_detect_line_endings', 1);
        $this->put('/test', "foo\r\rbar\rbaz");
        $h = fopen("{$this->basePath}/test", "r"); 
        $this->assertEquals("foo\r", fgets($h));
        $this->assertEquals("\r", fgets($h));
        $this->assertEquals("bar\r", fgets($h));
        $this->assertEquals("baz", fgets($h));
        $this->assertFalse(fgets($h));
    }

    /**
     * @group fgets
     * @group filesystemfunc
     */
    function testFgetsWithLength()
    {
        $nl = PHP_EOL;
        $this->put('/test', "foobar{$nl}bazqux");
        $h = fopen("{$this->basePath}/test", "r"); 

        // WTF? this length parameter is bizarre!
        $this->assertEquals("foo", fgets($h, 4));
        $this->assertEquals("bar", fgets($h, 4));
        $this->assertEquals($nl, fgets($h, 4));
        $this->assertEquals("baz", fgets($h, 4));
        $this->assertEquals("qux", fgets($h, 4));
        $this->assertFalse(fgets($h));
    }
}
