<?php
namespace Defile\Test\FileSystem;

use Defile\Stream;
use Defile\Node;

abstract class TestCase extends \Defile\Test\TestCase
{
    public $fileSystem;

    protected abstract function createFileSystem();

    function setUp()
    {
        $this->fileSystem = $this->createFileSystem();
    }

    function testTouchExistsWithLeadingSlash()
    {
        $this->assertFalse($this->fileSystem->exists('/foo'));
        $this->fileSystem->touch('/foo');
        $this->assertTrue($this->fileSystem->exists('/foo'));
    }

    function testTouchExistsWithoutLeadingSlash()
    {
        $this->assertFalse($this->fileSystem->exists('foo'));
        $this->fileSystem->touch('foo');
        $this->assertTrue($this->fileSystem->exists('foo'));
    }

    function testTouchGetNodeWithLeadingSlash()
    {
        $start = time();
        $this->fileSystem->touch('/foo');
        $node = $this->fileSystem->getNode('/foo');
        $this->assertInstanceOf(Node::class, $node);
        $this->assertEquals(Node::FILE, $node->type);
        $this->assertEquals('/foo', $node->path);
        $this->assertWithin(1, $node->mtime, $start);
        $this->assertWithin(1, $node->atime, $start);
        $this->assertWithin(1, $node->ctime, $start);
    }

    function testTouchGetNodeWithMixedLeading()
    {
        $this->fileSystem->touch('foo');
        $node = $this->fileSystem->getNode('/foo');
        $this->assertInstanceOf(Node::class, $node);
        $this->assertEquals(Node::FILE, $node->type);
        $this->assertEquals('/foo', $node->path);
    }

    function testTouchGetNodeWithoutLeadingSlash()
    {
        $this->fileSystem->touch('foo');
        $node = $this->fileSystem->getNode('foo');
        $this->assertInstanceOf(Node::class, $node);

        // $path should have been normalised by this stage. See implementation notes
        // in Defile\FileSystem
        $this->assertEquals(Node::FILE, $node->type);
        $this->assertEquals('/foo', $node->path);
    } 

    function testGetNodeNonexistent()
    {
        $node = $this->fileSystem->getNode('/foo');
        $this->assertNull($node);
    }

    function testMkdirGetNode()
    {
        $this->fileSystem->mkdir('/foo');
        $node = $this->fileSystem->getNode('foo');
        $this->assertInstanceOf(Node::class, $node);
        $this->assertEquals(Node::DIR, $node->type);
        $this->assertEquals('/foo', $node->path);
    }
}
