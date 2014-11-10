<?php
namespace Defile\Test\StreamWrapper\Comparison;

use Defile\Node;

trait Mkdir
{
    /**
     * @group mkdir
     * @group filesystemfunc
     */
    function testMkdir()
    {
        $result = mkdir("{$this->basePath}/foo");
        $this->assertTrue($result);
        $this->assertEquals(Node::DIR, $this->fileSystem->getNode("/foo")->type);
    }

    /**
     * @group mkdir
     * @group filesystemfunc
     */
    function testMkdirInNonexistent()
    {
        $result = mkdir("{$this->basePath}/foo/bar");
        $this->assertWarning("mkdir(): No such file or directory");
        $this->assertFalse($result);
    }

    /**
     * @group mkdir
     * @group filesystemfunc
     */
    function testMkdirFileExists()
    {
        $this->put("/foo", "abcd");
        $result = mkdir("{$this->basePath}/foo");
        $this->assertWarning("mkdir(): File exists");
        $this->assertFalse($result);
    }

    /**
     * @group mkdir
     * @group filesystemfunc
     */
    function testMkdirNestedFileExists()
    {
        $this->put("/foo", 'abcd');
        $result = mkdir("{$this->basePath}/foo/bar");
        $this->assertWarning("mkdir(): Not a directory");
        $this->assertFalse($result);
    }

    /**
     * @group mkdir
     * @group filesystemfunc
     */
    function testMkdirRecursive()
    {
        $result = mkdir("{$this->basePath}/foo/bar/baz", 0777, true);
        $node = $this->fileSystem->getNode("/foo/bar/baz");
        $this->assertEquals(Node::DIR, $node->type);
    }
}
