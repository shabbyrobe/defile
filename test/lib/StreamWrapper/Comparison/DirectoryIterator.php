<?php
namespace Defile\Test\StreamWrapper\Comparison;

use Defile\Node;

trait DirectoryIterator
{
    /**
     * @group directoryiterator
     * @group fsclass
     */
    function testDirectoryIteratorIsDir()
    {
        $this->put('/a/a/a', 'a');
        $this->put('/a/b/a', 'a');

        $names = [];
        foreach (new \DirectoryIterator("{$this->basePath}/a/") as $item) {
            $names[] = $item->getFilename();
            $this->assertTrue($item->isDir());
            $this->assertFalse($item->isFile());
        }
        sort($names);
        $this->assertEquals(['.', '..', 'a', 'b'], $names);
    }

    /**
     * @group directoryiterator
     * @group fsclass
     */
    function testDirectoryIteratorIsFile()
    {
        $this->put('/a/a', 'a');
        $this->put('/a/b', 'a');

        $names = [];
        foreach (new \DirectoryIterator("{$this->basePath}/a/") as $item) {
            $names[] = $item->getFilename();
            if (!$item->isDot()) {
                $this->assertTrue($item->isFile());
                $this->assertFalse($item->isDir());
            }
            else {
                $this->assertTrue($item->isDir());
                $this->assertFalse($item->isFile());
            }
        }
        sort($names);
        $this->assertEquals(['.', '..', 'a', 'b'], $names);
    }
}
