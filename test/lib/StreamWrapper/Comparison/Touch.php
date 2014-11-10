<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait Touch
{
    /**
     * @group filesystemfunc
     * @group touch
     */
    function testTouch()
    {
        $start = time();
        $result = touch("{$this->basePath}/foo");
        $this->assertTrue($result);
        $this->assertExists('/foo');
        $node = $this->fileSystem->getNode('/foo');
        $this->assertWithin(1, $node->mtime, $start);
        $this->assertWithin(1, $node->atime, $start);
        $this->assertWithin(1, $node->ctime, $start);
    }

    /**
     * @group touch
     * @group filesystemfunc
     */
    function testTouchMtime()
    {
        $result = touch("{$this->basePath}/foo", 1234);
        $this->assertTrue($result);
        $node = $this->fileSystem->getNode('/foo');
        $this->assertEquals(1234, $node->mtime);
    }

    /**
     * @group touch
     * @group filesystemfunc
     */
    function testTouchMtimeAtime()
    {
        $result = touch("{$this->basePath}/foo", 1234, 5678);
        $this->assertTrue($result);
        $node = $this->fileSystem->getNode('/foo');
        $this->assertEquals(1234, $node->mtime);
        $this->assertEquals(5678, $node->atime);
    } 

    /**
     * @group touch
     * @group filesystemfunc
     */
    function testTouchInNonexistentDirFails()
    {
        $result = touch("{$this->basePath}/foo/bar");
        $this->assertWarning(
            "touch(): Unable to create file {$this->basePath}/foo/bar because No such file or directory"
        );
        $this->assertFalse($result);
    }

    /**
     * @group touch
     * @group filesystemfunc
     */
    function testTouchWhenDirIsFileFails()
    {
        $this->put('/foo', 'abcd');
        $result = touch("{$this->basePath}/foo/bar");
        $this->assertWarning(
            "touch(): Unable to create file {$this->basePath}/foo/bar because Not a directory"
        );
        $this->assertFalse($result);
    }
}
