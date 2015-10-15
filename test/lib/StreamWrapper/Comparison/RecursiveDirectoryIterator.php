<?php
namespace Defile\Test\StreamWrapper\Comparison;

use Defile\Node;

trait RecursiveDirectoryIterator
{
    /**
     * @group recursivedirectoryiterator
     * @group fsclass
     */
    function testRecursiveDirectoryIterator()
    {
        $this->put('/a/a/a', '1');
        $this->put('/a/b/a', '1');
        $this->put('/a/c'  , '1');

        $iter = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator("{$this->basePath}/a/"), 
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iter as $item) {
            $name = $item->getFilename();

            // direct fs calls and streamwrapper calls can prioritise . and .. differently
            if ($name == '.' || $name == '..') {
                continue;
            }
            $names[] = [
                $item->getPathname(), 
                $item->isDir()
            ];
        }

        $expected = [
            [$this->tpl('path', ['file'=>'/a/a/a']), false],
            [$this->tpl('path', ['file'=>'/a/a']), true],
            [$this->tpl('path', ['file'=>'/a/b/a']), false],
            [$this->tpl('path', ['file'=>'/a/b']), true],
            [$this->tpl('path', ['file'=>'/a/c']), false],
        ];
        $this->assertEquals($expected, $names);
    }
}
