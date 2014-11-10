<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait Stat
{
    /**
     * @group stat
     * @group filesystemfunc
     */
    function testStat()
    {
        $this->put('/test', 'abcd');
        $stat = stat("{$this->basePath}/test");
        $this->assertEquals(
            [
                0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 
                'dev', 'ino', 'mode', 'nlink', 'uid', 'gid', 'rdev', 
                'size', 'atime', 'mtime', 'ctime', 'blksize', 'blocks'
            ], 
            array_keys($stat)
        );
        $this->assertEquals(0100666, $stat['mode']);
        $this->assertInternalType('int', $stat['ino']);
        $this->assertNotEquals(0, $stat['ino']);
    }

    /**
     * @group stat
     * @group filesystemfunc
     */
    function testStatDir()
    {
        $this->fileSystem->mkdir('/test');
        $stat = stat("{$this->basePath}/test");
        $this->assertEquals(040777, $stat['mode']);
    }

    /**
     * @group stat
     * @group filesystemfunc
     */
    function testStatNonexistent()
    {
        $result = stat("{$this->basePath}/test");
        $this->assertWarning("stat(): stat failed for {$this->basePath}/test");
        $this->assertFalse($result);
    }
}
