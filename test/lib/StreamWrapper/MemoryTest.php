<?php
namespace Defile\Test\StreamWrapper;

/**
 * @group memory
 * @group streamwrapper
 */
class MemoryTest extends TestCase
{
    use Comparison\All;
    use Stream\All;
    
    public $currentStream;

    function createFileSystem()
    {
        $fs = new TestMemoryFileSystem;
        $fs->testCase = $this;
        return $fs;
    }
}

if (!class_exists(__NAMESPACE__.'\TestMemoryFileSystem')) {
    class TestMemoryFileSystem extends \Defile\FileSystem\Memory
    {
        public $testCase;

        protected function createStream(\Defile\Node $node, $streamMode)
        {
            $stream = parent::createStream($node, $streamMode);
            $this->testCase->currentStream = $stream;
            return $stream;
        }
    }
}
