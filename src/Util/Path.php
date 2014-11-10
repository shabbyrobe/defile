<?php
namespace Defile\Util;

class Path
{
    private function __construct()
    {}

    public static function normalise($path)
    {
        $path = ltrim($path);
        $slash = '/';
        if (!$path)
            throw new \InvalidArgumentException();

        $split = explode('/', $path);
        $new = [];
        $recent = null;

        $initial = 0;

        foreach ($split as $part) {
            if (!$new && $part == '')
                ++$initial;
            elseif ($part == ''|| $part == '.')
                continue;
            elseif ($part != '..' || (!$initial && !$new) || ($new && $recent == '..'))
                $new[] = $recent = $part;
            else
                array_pop($new);
        }
        $newStr = ($initial ? '/' : '').implode($slash, $new);
        return $newStr;
    }

    public static function isabs($path)
    {
        return $path && $path[0] == '/';
    }

    public static function split($path)
    {
        return preg_split("@/+@", $path, null, PREG_SPLIT_NO_EMPTY);
    }
}
