<?php
namespace Defile\Test\StreamWrapper\Comparison;

trait All
{
    // FILESYSTEM FUNCTIONS
    //use Chgrp;
    use Chmod;
    //use Chown;
    use Copy;
    use Feof;
    //use Fflush;
    use Fgetc;
    use FgetCsv;
    use Fgets;
    //use Fgetss;
    use FileExists;
    use FileGetContents;
    use FilePutContents;
    use File;
    use FileAtime;
    use FileCtime;
    //use FileGroup;
    use FileInode;
    use FileMtime;
    //use FileOwner;
    //use FilePerms;
    use FileSize;
    use FileType;
    //use Flock;
    use Fopen;
    use Fpassthru;
    //use FputCsv;
    //use Fputs;
    use Fread;
    //use Fscanf;
    use Fseek;
    use Fstat;
    use Ftell;
    use Ftruncate;
    use Fwrite;
    use IsDir;
    //use IsExecutable;
    use IsFile;
    //use IsLink;
    //use IsReadable;
    //use IsWritable; //also test is_writeable
    use Mkdir;
    //use ParseIniFile;
    //use Readfile;
    use Rename;
    use Rewind;
    use Rmdir;
    use Stat;
    use Touch;
    use Unlink;

    // DIRECTORY FUNCTIONS
    // http://www.php.net/manual/en/ref.dir.php
    use CloseDir;
    use Dir;
    use OpenDir;
    use ReadDir;
    use RewindDir;
    use ScanDir;

    // FINFO EXTENSION
    //use Finfo;
 
    // FILESYSTEM CLASSES
    use DirectoryIterator;
    //use GlobIterator;
    use RecursiveDirectoryIterator;
    //use SplFileInfo;
    //use SplFileObject;

    // STREAM FUNCTIONS
    //use StreamSetBlocking;
    //use StreamSetTimeout;
    //use StreamSetWriteBuffer;

    // UNSUPPORTED
    // CurlFile
    // glob
    // lchmod
    // link
    // readlink
    // symlink
    // ZipArchive
}
