<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait Rename
{
    /**
     * @group rename
     * @group filesystemfunc
     */
    function testRenameFiles()
    {
        $this->put('/bar', 'abcd');
        $result = rename("{$this->basePath}/bar", "{$this->basePath}/baz");
        $this->assertTrue($result);
        $this->assertNotExists('/bar');
        $this->assertFileContains('/baz', 'abcd');
    }

    /**
     * @group rename
     * @group filesystemfunc
     */
    function testRenameInsideDir()
    {
        $this->put('/foo/bar', 'abcd');
        $result = rename("{$this->basePath}/foo/bar", "{$this->basePath}/foo/baz");
        $this->assertTrue($result);
        $this->assertNotExists('/foo/bar');
        $this->assertFileContains('/foo/baz', 'abcd');
    }

    /**
     * The command line syntax where the second argument to rename can be a destination
     * dir is not supported by PHP. FileSystem should support it, but the stream wrapper
     * should prevent that behaviour from manifesting via rename()
     * @group rename
     * @group filesystemfunc
     */
    function testRenameIntoDirFails()
    {
        $this->put('/foo/bar', 'abcd');
        $this->fileSystem->mkdir('/baz');
        $result = rename("{$this->basePath}/foo/bar", "{$this->basePath}/baz");
        $this->assertWarning(
            "rename({$this->basePath}/foo/bar,{$this->basePath}/baz): Is a directory"
        );
        $this->assertFalse($result);
    }

    /**
     * @group rename
     * @group filesystemfunc
     */
    function testRenameIntoNonExistentDirFails()
    {
        $this->put('/foo/bar', 'abcd');
        $result = rename("{$this->basePath}/foo/bar", "{$this->basePath}/qux/baz");
        $this->assertWarning(
            "rename({$this->basePath}/foo/bar,{$this->basePath}/qux/baz): No such file or directory"
        );
        $this->assertFalse($result);
    }

    /**
     * @group rename
     * @group filesystemfunc
     */
    function testRenameNonexistentFileIntoExistingDirFails()
    {
        $this->fileSystem->mkdir('/qux');
        $result = rename("{$this->basePath}/foo/bar", "{$this->basePath}/qux/baz");
        $this->assertWarning(
            "rename({$this->basePath}/foo/bar,{$this->basePath}/qux/baz): No such file or directory"
        );
        $this->assertFalse($result);
    }

    /**
     * @group rename
     * @group filesystemfunc
     */
    function testRenameIntoNonexistentFsFails()
    {
        $this->put("/foo", "abcd");
        $result = rename("{$this->basePath}/foo", "nonexistent://pants/boing");
        $this->assertPhpErrors([
            [
                [E_USER_WARNING, E_WARNING], 
                'rename(): Unable to find the wrapper "nonexistent" - '.
                'did you forget to enable it when you configured PHP?'
            ],
            [
                [E_USER_WARNING, E_WARNING],
                '/(No such file or directory|Cannot rename a file across wrapper types)/',
                !!'regex'
            ],
        ]);
        $this->assertFalse($result);
    }

    /**
     * @group rename
     * @group filesystemfunc
     */
    function testRenameNonexistentFileIntoNonexistentDirFails()
    {
        $result = rename("{$this->basePath}/foo/bar", "{$this->basePath}/qux/baz");
        $this->assertWarning(
            "rename({$this->basePath}/foo/bar,{$this->basePath}/qux/baz): No such file or directory"
        );
        $this->assertFalse($result);
    }
}
