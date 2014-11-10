<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait CloseDir
{
    /**
     * @group closedir
     * @group directoryfunc
     */
    function testCloseDir()
    {
        $this->put('/t/a', 'abcd');
        $this->put('/t/b', 'abcd');

        $dh = opendir("{$this->basePath}/t");
        $this->assertNotFalse(readdir($dh));

        // unlike the filesystem functions, closedir returns void
        $this->assertNull(closedir($dh));

        $result = readdir($dh);
        $this->assertWarningRegexp("/readdir\(\): \d+ is not a valid Directory resource/");
        $this->assertFalse($result);
    }
}
