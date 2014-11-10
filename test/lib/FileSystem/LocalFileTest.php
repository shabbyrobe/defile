<?php
namespace Defile\Test\FileSystem;

use Defile\Stream;
use Defile\Node;
use Defile\Test\Helpers;

/**
 * @group localfile
 * @group filesystem
 */
class LocalFileTest extends TestCase
{
    use Helpers\LocalCleanup;

    function setUp()
    {
        $this->tempPath = sys_get_temp_dir()."/".uniqid('defile-localtest-', true);
        mkdir($this->tempPath);
        parent::setUp();
    }

    function tearDown()
    {
        try {
            parent::tearDown();
        }
        finally {
            $this->deleteRecursive($this->tempPath);
        }
    }

    function createFileSystem()
    {
        return new \Defile\FileSystem\LocalFile($this->tempPath);
    }
}

