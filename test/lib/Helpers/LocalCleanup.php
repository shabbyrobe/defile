<?php
namespace Defile\Test\Helpers;

trait LocalCleanup
{
    protected function deleteRecursive($path)
    {
        $dir = new \RecursiveDirectoryIterator($path);
        foreach (new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::CHILD_FIRST) as $i) {
            if ($i->getFilename() == '.' || $i->getFilename() == '..') continue;
            if ($i->isDir())
                rmdir($i->getPathname());
            else
                unlink($i->getPathname());
        }
        rmdir($path);
    }
}
