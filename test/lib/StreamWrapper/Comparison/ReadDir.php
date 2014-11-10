<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait ReadDir
{
    /**
     * @group readdir
     * @group directoryfunc
     */
    function testReadDir()
    {
        $this->put('/t/a', 'abc');
        $this->put('/t/b', 'abc');

        // we can't test reading in the root of our VFS in the comparison test
        // because we don't get equivaliency with the Direct test - it's testing
        // in a nested temp dir all the time
        $dh = opendir("{$this->basePath}/t");
        $entries = [];
        $entries[] = readdir($dh);
        $entries[] = readdir($dh);
        $entries[] = readdir($dh);
        $entries[] = readdir($dh);

        // the entries come out in filesystem order (not necessarily naturally ordered)
        sort($entries);
        $this->assertEquals(['.', '..', 'a', 'b'], $entries);

        $result = readdir($dh);
        $this->assertFalse($result);
    }
}
