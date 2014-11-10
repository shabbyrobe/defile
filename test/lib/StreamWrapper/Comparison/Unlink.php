<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait Unlink
{
    /**
     * @group unlink
     * @group filesystemfunc
     */
    function setUpUnlink()
    {
        $this->templates['unlinkNonexistent'] = 'unlink({basePath}{file}): No such file or directory';
        $this->templates['unlinkIsDir'] = 'unlink({basePath}{file}): Is a directory';
    }

    /**
     * @group unlink
     * @group filesystemfunc
     */
    function testUnlinkFile()
    {
        $this->put('/foo', 'abcd');
        unlink("{$this->basePath}/foo");
        $this->assertNotExists('/foo');
    }

    /**
     * @group unlink
     * @group filesystemfunc
     */
    function testUnlinkNonExistentFile()
    {
        $file = "/foo";
        unlink("{$this->basePath}{$file}");
        $this->assertWarning($this->tpl('unlinkNonexistent', ['file'=>$file]));
    }

    /**
     * @group unlink
     * @group filesystemfunc
     */
    function testUnlinkDirFails()
    {
        $dir = "/dir";
        $this->fileSystem->mkdir($dir);

        unlink("{$this->basePath}$dir");
        $this->assertWarning($this->tpl('unlinkIsDir', ['file'=>$dir]));
    }
}
