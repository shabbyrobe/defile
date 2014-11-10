<?php
namespace Defile\Test\FileSystem;

use Defile\Stream;
use Defile\Node;

/**
 * @group memory
 * @group filesystem
 */
class MemoryTest extends TestCase
{
    function createFileSystem()
    {
        $fs = new \Defile\FileSystem\Memory;
        return $fs;
    }
}
