<?php
namespace Defile;

/**
 * Aims to match PHP's behaviour as closely as possible. All hacks, inconsistencies
 * and compatibility quirks belong in here - the filesystem API itself should be kept
 * free of PHP-related gotchas.
 *
 * You can't use static properties with these classes - 5.5.13 on Ubuntu causes
 * a segmentation fault if you try. self:: and static:: both fail
 *
 * It also looks like you can't use traits either.
 */
class StreamWrapper extends Util\Object
{
    public $context;

    private $stream;
    private $streamOpenMode;

    private $dir;
    private $dirHandle;

    /**
     * @return bool
     */
    public function dir_closedir()
    {
        $this->dir = null;
        $this->dirHandle = null;
        return true;
    }

    /**
     * @param string $path
     * @param int $options
     * @return bool
     */
    public function dir_opendir($path, $options)
    {
        if ($options !== 0)
            throw new \UnexpectedValueException();

        list ($url, $fs) = $this->parsePath($path);

        $ret = true;
        $this->dir = [null, $url, $fs];  
        try {
            $ret = $this->dirRewind();
        }
        catch (FileException $fex) {
            // PHP's stream layer generates its own error here if you return false. 
            // You lose detail, but those are the breaks.
            // trigger_error("opendir($path): {$fex->getDefaultMessage()}", E_USER_WARNING);
            $ret = false;
        }
        catch (\Exception $ex) {
            trigger_error("opendir($path): {$ex->getMessage()}", E_USER_ERROR);
            $ret = false;
        }
        return $ret;
    }

    /**
     * @return string
     */
    public function dir_readdir()
    {
        if (!$this->dirHandle) {
            return false;
        }
        elseif ($this->dirHandle->valid()) {
            $item = $this->dirHandle->current();
            $this->dirHandle->next();
            return $item;
        }
        else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function dir_rewinddir()
    {
        try {
            return $this->dirRewind();
        }
        catch (\FileException $fex) {
            return false;
        }
    }

    private function dirRewind()
    {
        if (!$this->dir) 
            return false;
        
        list (, $url, $fs) = $this->dir;
        $iterator = $fs->iterate($url['path']);
        $gen = function() use ($url, $iterator) {
            yield '.';
            if ($url['path'] != '/' && $url['path'] != '')
                yield '..';

            foreach ($iterator as $item)
                yield $item;
        };
        $this->dirHandle = $gen();
        return true;
    }

    /**
     * @param string $path
     * @param int $mode
     * @param int $options STREAM_MKDIR_RECURSIVE, ...? not documented
     * @return bool
     */
    public function mkdir($path, $mode, $options)
    {
        list ($url, $fs) = $this->parsePath($path);
        try {
            return $fs->mkdir($url['path'], $mode, $options);
        }
        catch (FileException $fex) {
            trigger_error("mkdir(): {$fex->getDefaultMessage()}", E_USER_WARNING);
            return false;
        }
    }

    /**
     * @param string $pathFrom
     * @param string $pathTo
     * @return bool
     */
    public function rename($pathFrom, $pathTo)
    {
        list ($fromUrl, $fromFs) = $this->parsePath($pathFrom);
        list ($toUrl, $toFs) = $this->parsePath($pathTo);

        if ($fromFs != $toFs)
            throw new \LogicException("Cross fs rename not supported yet");

        $toNode = $toFs->getNode($toUrl['path']);
        if ($toNode && $toNode->type == Node::DIR) {
            trigger_error(
                "rename($pathFrom,$pathTo): ".FileException::$messages[FileException::EISDIR], E_USER_WARNING
            );
            return false;
        }

        try {
            return $fromFs->rename($fromUrl['path'], $toUrl['path']);
        }
        catch (FileException $fex) {
            trigger_error("rename($pathFrom,$pathTo): {$fex->getMessage()}", E_USER_WARNING);
        }
    }

    /**
     * @param string $path
     * @param int $options
     * @return bool
     */
    public function rmdir($path, $options)
    {
        list ($url, $fs) = $this->parsePath($path);

        try {
            $fs->rmdir($url['path']);
            return true;
        }
        catch (FileException $fex) {
            trigger_error("rmdir($path): {$fex->getDefaultMessage()}", E_USER_WARNING);
            return false;
        }
    }

    /**
     * @param int $castAs
     * @return resource
     */
    public function stream_cast($castAs)
    {
        if ($this->stream)
            return $this->stream->cast($castAs);
        else
            return false;
    }

    /**
     * @return void
     */
    public function stream_close()
    {
        if ($this->stream) {
            $this->stream->close();
            $this->stream = null;
        }
    }

    /**
     * @return bool
     */
    public function stream_eof()
    {
        if ($this->stream)
            return $this->stream->eof();
        else
            return true;
    }

    /**
     * @return bool
     */
    public function stream_flush()
    {

        if ($this->stream)
            return $this->stream->flush();
        else
            return false;
    }

    /**
     * @param int $operation
     * @return bool
     */
    public function stream_lock()
    {
        throw new \BadMethodCallException("Not yet supported");
    }

    /**
     * @param string $path
     * @param int $option
     * @param mixed $value 
     * @return bool
     */
    public function stream_metadata($path, $option, $value)
    {
        list ($url, $fs) = $this->parsePath($path);
        
        if ($option == STREAM_META_TOUCH) {
            $mtime = isset($value[0]) ? $value[0] : null;
            $atime = isset($value[1]) ? $value[1] : null;
            try {
                $node = $fs->touch($url['path'], $mtime, $atime);
                return $node == true;
            }
            catch (FileException $fex) {
                trigger_error(
                    "touch(): Unable to create file $path because {$fex->getDefaultMessage()}", 
                    E_USER_WARNING
                );
            }
        }

        elseif ($option == STREAM_META_OWNER_NAME || $option == STREAM_META_OWNER) {
            return $fs->setMeta($url['path'], FileSystem::M_OWNER, $value);
        }

        elseif ($option == STREAM_META_GROUP_NAME || $option == STREAM_META_GROUP) {
            return $fs->setMeta($url['path'], FileSystem::M_GROUP, $value);
        }

        elseif ($option == STREAM_META_ACCESS) {
            $value = $value & ~umask(); 
            try {
                return $fs->setMeta($url['path'], FileSystem::M_PERMS, $value);
            }
            catch (FileException $fex) {
                trigger_error("chmod(): {$fex->getDefaultMessage()}", E_USER_WARNING);
            }
        }

        else {
            throw new \InvalidArgumentException();
        }
    }

    /**
     * @param string $path
     * @param string $mode
     * @param int $options
     * @param string &$openedPath 
     * @return bool
     */
    public function stream_open($path, $mode, $options, &$openedPath)
    {
        $raiseErrors = $options & STREAM_REPORT_ERRORS;
        list ($url, $fs) = $this->parsePath($path);

        $this->stream = null;
        try {
            $this->stream = $fs->open($url['path'], $mode);
        }
        catch (FileException $fex) {
            // Apparently you can fopen directories in read mode in PHP, they just show
            // an empty string. The manual says 'may'... this could change.
            if (($mode == 'r' || $mode == 'rb') && $fex->getCode() == FileException::EISDIR) {
                $node = $fs->getNode($url['path']);
                $this->stream = new Stream\Memory($fs, $node, Stream::READ, (object)['data'=>'']);
            }
            elseif ($raiseErrors) {
                trigger_error($fex->getMessage(), E_USER_WARNING);
            }
        }

        if ($this->stream) {
            $this->streamOpenMode = $mode;
        }

        return $this->stream == true;
    }

    public function parsePath($path)
    {
        $url = parse_url($path);
        if (!isset($url['path']))
            $url['path'] = '/';

        $fs = StreamRegistry::instance()->get($url['scheme'], $url['host']);
        return [$url, $fs];
    }

    /**
     * @param int $count
     * @return string
     */
    public function stream_read($count)
    {
        $buf = null;
        try {
            $buf = $this->stream->read($count);
        }
        catch (FileException $fex) {
            if ($fex->getCode() == FileException::ENOTREADABLE) {
                return false;
            }
            else {
                throw $fex;
            }
        }
        if ($buf !== false && !is_string($buf))
            throw new \UnexpectedValueException();

        return $buf;
    }

    /**
     * @param int $offset
     * @param int $whence
     * @return bool
     */
    public function stream_seek($offset, $whence=SEEK_SET)
    {
        if ($this->stream) {
            $result = $this->stream->seek($offset, $whence);
            return $result;
        }
        else {
            return false;
        }
    }

    /**
     * @param int $option
     * @param int $arg1
     * @param int $arg2 
     * @return bool
     */
    public function stream_set_option($option, $arg1, $arg2)
    {
        throw new \BadMethodCallException("Not yet supported");
    }

    /**
     * @return array
     */
    public function stream_stat()
    {
        if ($this->stream) 
            return $this->stream->getNode()->stat();
        else
            return false;
    }

    /**
     * @return int
     */
    public function stream_tell()
    {
        if ($this->stream)
            return $this->stream->tell();
        else
            return false;
    }

    /**
     * @param int $newSize
     * @return bool
     */
    public function stream_truncate($newSize)
    {
        if ($this->stream) {
            try {
                return $this->stream->resize($newSize);
            }
            catch (FileException $fex) {
                if ($fex->getCode() == FileException::ENOTWRITABLE) {
                    return false;
                }
                else {
                    throw $fex;
                }
            }
        }
        else {
            return false;
        }
    }

    /**
     * @param stirng $data
     * @return int
     */
    public function stream_write($data)
    {
        $ret = 0;
        if ($this->stream) {
            // From FTM: "If you have opened the file in append mode, any data you write
            // will always be appended regardless of the file position"
            if ($this->streamOpenMode[0] == 'a') {
                $pos = $this->stream->tell();
                $this->stream->seek(0, SEEK_END);
            }

            try {
                $ret = $this->stream->write($data);

                // aaand let's put the stream back where we found it.
                if ($this->streamOpenMode[0] == 'a') {
                    $this->stream->seek($pos);
                }
            }
            catch (FileException $fex) {
                if ($fex->getCode() == FileException::ENOTWRITABLE) {
                    $ret = false;
                }
                else {
                    throw $fex;
                }
            }
            
        }
        return $ret;
    }

    /**
     * @param string $path
     * @return bool
     */
    public function unlink($path)
    {
        list ($url, $fs) = $this->parsePath($path);

        try {
            $fs->rm($url['path']);
            return true;
        }
        catch (FileException $fex) {
            trigger_error("unlink($path): {$fex->getDefaultMessage()}", E_USER_WARNING);
            return false;
        }
    }

    /**
     * @param string $path
     * @param int $flags
     * @return array
     */
    public function url_stat($path, $flags)
    {
        list ($url, $fs) = $this->parsePath($path);
        try {
            $node = $fs->getNode($url['path']);
        }
        catch (FileException $fex) {
            return false;
        }
        if ($node)
            return $node->stat();
        else
            return false;
    }
}

