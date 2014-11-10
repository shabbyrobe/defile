<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait FileInode
{
    /**
     * @group fileinode
     * @group filesystemfunc
     */
    function testFileInode()
    {
        $this->put('/test', 'abcd');
        $inode = fileinode("{$this->basePath}/test");
        $this->assertInternalType('integer', $inode);
        $this->assertNotEquals(0, $inode);
    }

    /**
     * @group fileinode
     * @group filesystemfunc
     */
    function testFileInodeDir()
    {
        $this->fileSystem->mkdir('/test');
        $inode = fileinode("{$this->basePath}/test");
        $this->assertInternalType('integer', $inode);
        $this->assertNotEquals(0, $inode);
    }

    /**
     * @group fileinode
     * @group filesystemfunc
     */
    function testFileInodeNonexistent()
    {
        $result = fileinode("{$this->basePath}/test");
        $this->assertWarning("fileinode(): stat failed for {$this->basePath}/test");
        $this->assertFalse($result);
    }
}
