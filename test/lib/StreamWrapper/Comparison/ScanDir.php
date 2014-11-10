<?php
namespace Defile\Test\StreamWrapper\Comparison;

use Defile\Test\StreamWrapper;

trait ScanDir
{
    /**
     * @group scandir
     * @group directoryfunc
     */
    function testScanDir()
    {
        $this->put('/t/a', 'abcd');
        $this->put('/t/b', 'abcd');

        $result = scandir("{$this->basePath}/t");
        $this->assertEquals(['.', '..', 'a', 'b'], $result);
    }

    /**
     * @group scandir
     * @group directoryfunc
     */
    function testScanDirWithFile()
    {
        $this->put('/t/a', 'abcd');
        $this->put('/t/b', 'abcd');

        $result = scandir("{$this->basePath}/t/a");
        $errors = [
            [
                [E_WARNING, E_USER_WARNING], 
                $this->tpl('openDirFails', ['func'=>'scandir', 'file'=>'/t/a', 'msg'=>'Not a directory'])
            ],
        ];

        // FIXME: It could be worth opening up a discussion on internals about this. If I raise my
        // own warning from StreamWrapper, I get three errors here instead of the two that 
        // vanilla scandir raises, but at least one of the three contains the right "Not a
        // directory" message. The stream handling layer seems to interpret a 'return false' from
        // this function as ENOENT in all circumstances.
        if (get_class($this) ==  StreamWrapper\DirectTest::class) {
            $errors[] = [[E_WARNING, E_USER_WARNING], "scandir(): (errno 20): Not a directory"];
        }
        else {
            $errors[] = [[E_WARNING, E_USER_WARNING], "scandir(): (errno 2): No such file or directory"];
        }
        $this->assertPhpErrors($errors);
        $this->assertFalse($result);
    }

    /**
     * @group scandir
     * @group directoryfunc
     */
    function testScanDirWithNonexistent()
    {
        $result = scandir("{$this->basePath}/nope");
        $errors = [
            [
                [E_WARNING, E_USER_WARNING], 
                $this->tpl('openDirFails', ['func'=>'scandir', 'file'=>'/nope', 'msg'=>'No such file or directory'])
            ],
            [
                [E_WARNING, E_USER_WARNING], "scandir(): (errno 2): No such file or directory"
            ],
        ];
        $this->assertPhpErrors($errors);
        $this->assertFalse($result);
    }
}
