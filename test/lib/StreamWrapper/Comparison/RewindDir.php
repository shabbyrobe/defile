<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait RewindDir
{
    /**
     * @group rewinddir
     * @group directoryfunc
     */
    function testRewindDir()
    {
        $this->put('/t/a', 'abc');
        $this->put('/t/b', 'abc');

        $dh = opendir("{$this->basePath}/t");
        $first = readdir($dh);
        $second = readdir($dh);

        rewinddir($dh);

        $this->assertEquals($first, readdir($dh));
        $this->assertEquals($second, readdir($dh));
    }

    /**
     * @group rewinddir
     * @group directoryfunc
     */
    function testRewindUnreadDir()
    {
        $this->put('/t/a', 'abc');
        $this->put('/t/b', 'abc');

        $dh = opendir("{$this->basePath}/t");
        rewinddir($dh);

        $this->assertNotFalse(readdir($dh));
        $this->assertNotFalse(readdir($dh));
    }
}
