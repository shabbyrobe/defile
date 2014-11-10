<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait OpenDir
{
    /**
     * @group opendir
     * @group directoryfunc
     */
    function testOpenDir()
    {
        $this->fileSystem->mkdir("/test");
        $dh = opendir("{$this->basePath}/test");
        $this->assertInternalType('resource', $dh);
    }

    /**
     * @group opendir
     * @group directoryfunc
     */
    function testOpenDirFileFails()
    {
        $this->put('/test', 'abcd');
        $result = opendir("{$this->basePath}/test");
        $this->assertWarning($this->tpl(
            'openDirFails', 
            ['func'=>'opendir', 'file'=>'/test', 'msg'=>'Not a directory']
        ));
        $this->assertFalse($result);
    }
}
