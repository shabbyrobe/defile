<?php
namespace Defile;

class FileException extends \RuntimeException
{
    const ENOENT = 1;
    const EISDIR = 2;
    const ENOTDIR = 3;
    const ENOTEMPTY = 4;
    const EEXIST = 5;
    const EPERM = 6;
    const EACCES = 7;
    const EACCESS = 7;

    const ENOTWRITABLE = 8;
    const ENOTREADABLE = 9;

    static $messages = [
        self::ENOENT       => "No such file or directory",
        self::EISDIR       => "Is a directory",
        self::ENOTDIR      => "Not a directory",
        self::ENOTEMPTY    => "Directory not empty",
        self::EEXIST       => "File exists",
        self::EACCESS      => "Permission denied",

        self::ENOTWRITABLE => "Not writable",
        self::ENOTREADABLE => "Not readable",
    ];

    public function __construct($message=null, $code=null)
    {
        if (!$message && isset(static::$messages[$code])) { 
            $message = static::$messages[$code];
        }
        parent::__construct($message, $code);
    }

    public function getDefaultMessage()
    {
        $code = $this->getCode();
        if (isset(static::$messages[$code]))
            return static::$messages[$code];
    }

    public static function __callStatic($name, $args)
    {
        if (defined("static::$name")) {
            return new static(isset($args[0]) ? $args[0] : null, constant("static::$name"));
        }
        else {
            throw new \BadMethodCallException();
        }
    }
}

FileException::$messages[FileException::EACCES] = FileException::$messages[FileException::EACCESS];

