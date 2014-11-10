<?php
namespace Defile\Test\StreamWrapper;

use Defile\Stream;
use Defile\Node;

abstract class TestCase extends \Defile\Test\PhpErrorHandlingTestCase
{
    public $fileSystem;
    public $basePath;

    protected $templates = [];

    protected abstract function createFileSystem();

    public function setUp()
    {
        parent::setUp();

        umask(0);
        clearstatcache();
        ini_set('auto_detect_line_endings', 0);

        $this->registeredStreams = stream_get_wrappers();
        $this->fileSystem = $this->createFileSystem(); 
        $this->basePath = 'defiletest://test';

        if ($this->fileSystem) {
            $registry = \Defile\StreamRegistry::instance()->add('defiletest', 'test', $this->fileSystem);
        }

        $this->templates['openFails'] = '{func}({basePath}{file}): failed to open stream: "Defile\StreamWrapper::stream_open" call failed';
        $this->templates['openDirFails'] = '{func}({basePath}{file}): failed to open dir: "Defile\StreamWrapper::dir_opendir" call failed';

        $rc = new \ReflectionClass($this);
        foreach ($rc->getMethods() as $m) {
            // run all methods that start with setUp (but aren't only 'setUp')
            if (stripos($m->name, 'setUp')===0 && strlen($m->name) > 5)
                $m->invoke($this);
        }
    }

    public function tearDown()
    {
        \Defile\StreamRegistry::instance()->remove('defiletest', 'test');
        if (stream_get_wrappers() != $this->registeredStreams)
            throw new \UnexpectedValueException();

        parent::tearDown();
    }

    public function tpl($name, $vars)
    {
        if (!isset($this->templates[$name]))
            throw new \InvalidArgumentException();

        $tpl = $this->templates[$name];
        $tok = [];
        $tok['{basePath}'] = $this->basePath;
        foreach ($vars as $k=>$v) {
            $tok['{'.$k.'}'] = $v;
        }

        // this is getting kinda gross.
        if (!isset($tok['{msg}']))
            $tok['{msg}'] = "No such file or directory";

        return strtr($tpl, $tok);
    }

    protected function createBuffer($len)
    {
        if ($len % 8 != 0)
            throw new \Exception("Must be multiple of 8");

        $buf = "";
        for ($i = 1, $cnt = $len / 8; $i <= $cnt; $i++) {
            $buf .= sprintf("%07d\n", $i);
        }
        return $buf;
    }

    protected function put($node, $contents)
    {
        if (is_string($node))
            $node = new Node($node);

        $dir = dirname($node->path);
        if (!$this->fileSystem->exists($dir)) {
            $this->fileSystem->mkdir($dir, 0777, STREAM_MKDIR_RECURSIVE);
        }
        $s = $this->fileSystem->open($node->path, 'w');
        $s->write($contents);
    }

    public function assertFileContains($file, $contents)
    {
        $stream = $this->fileSystem->open($file, 'r');
        $this->assertTrue($stream instanceof Stream, "$file does not exist");
        $streamContents = $stream->read();
        $this->assertEquals($contents, $streamContents);
    }

    public function assertExists($file)
    {
        $exists = $this->fileSystem->exists($file);
        $this->assertTrue($exists);
    }

    public function assertNotExists($file)
    {
        $exists = $this->fileSystem->exists($file);
        $this->assertFalse($exists);
    }

    /**
     * FIXME: PHPUnit can't do dynamic data providers
     */
    public function __call($name, $args)
    {
        if (strpos($name, 'dataModes')===0) {
            $modes = [];
            $allowedModes = ['R', 'A', 'W', 'X', 'C'];
            $allowedMods = ['B'=>'b', 'P'=>'+'];

            $modeString = substr($name, 9);
            foreach (explode('_', $modeString) as $m) {
                if (!$m) continue;

                if (strlen($m) > 3)
                    throw new \InvalidArgumentException("Unknown mode string {$m}");

                if (!in_array($m[0], $allowedModes)) {
                    throw new \InvalidArgumentException("Unknown mode type {$m[0]}");
                }

                $realMode = strtolower($m[0]);
                if (isset($m[1])) {
                    if (!isset($allowedMods[$m[1]])) 
                        throw new \InvalidArgumentException("Unknown modifier {$m[1]}");
                    $realMode .= $allowedMods[$m[1]];
                }
                if (isset($m[2])) {
                    if (!isset($allowedMods[$m[2]])) 
                        throw new \InvalidArgumentException("Unknown modifier {$m[2]}");
                    $realMode .= $allowedMods[$m[2]];
                }
                $modes[] = [$realMode];
            }
            return $modes;
        }
        else {
            throw new \BadMethodCallException("Unknown method $name");
        }
    }
}
