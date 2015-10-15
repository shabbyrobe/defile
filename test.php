<?php
$usage = "Test runner
Usage: test/run.php [--filter=<expr>]
           [--coverage-html=<outpath>] 
           [--exclude-group=<group>]
           [--group=<group>]
           [--throw-on-error]

This runner captures warnings in a different way to PHPUnit. If you want
to see stack traces for debugging when PHP errors, warnings, etc are thrown,
pass --throw-on-error.
";

$basePath = __DIR__;
$testPath = __DIR__."/test";

require_once "$basePath/vendor/autoload.php";
require_once "$testPath/config.php";
error_reporting(E_ALL);

autoload_namespace('Defile\Test', $testPath.'/lib', ['prepend'=>true]);

// if it gets too tricky to find your bug, enable this:
// \Defile\StreamWrapper::$logHandle = fopen("php://stdout", "w");

stream_chunk_ensure_quirk();

$options = array(
    'coverage-html'=>null,
    'filter'=>null,
    'exclude-group'=>null,
    'group'=>null,
);
$options = array_merge(
    $options,
    getopt('q:', array('help', 'filter:', 'coverage-html:', 'exclude-group:', 'group:', 'throw-on-error')) ?: []
);
$help = array_key_exists('help', $options);
if ($help) {
    echo $usage;
    exit;
}

$throwOnError = array_key_exists('throw-on-error', $options);
define('THROW_ON_ERROR', $throwOnError);

$config = array();

$groups = $options['group'] ? explode(',', $options['group']) : null;
$args = array(
    'reportDirectory'=>$options['coverage-html'],
    'filter'=>$options['filter'],
    'excludeGroups'=>explode(',', $options['exclude-group']),
    'groups'=>$groups,
    'strict'=>true,
    'processIsolation'=>false,
    'backupGlobals'=>false,
    'backupStaticAttributes'=>false,
    'convertErrorsToExceptions'=>$throwOnError,
    'convertNoticesToExceptions'=>$throwOnError,
    'convertWarningsToExceptions'=>$throwOnError,
    'addUncoveredFilesFromWhitelist'=>true,
    'processUncoveredFilesFromWhitelist'=>true,
);

$masterSuite = new PHPUnit_Framework_TestSuite();

$suite = new PHPUnit_Framework_TestSuite();
suite_add_dir($suite, $testPath.'/lib/Unit/');
$masterSuite->addTest($suite);

$suite = new PHPUnit_Framework_TestSuite();
suite_add_dir($suite, $testPath.'/lib/FileSystem/');
$masterSuite->addTest($suite);

$suite = new PHPUnit_Framework_TestSuite();
suite_add_dir($suite, $testPath.'/lib/StreamWrapper/');
$masterSuite->addTest($suite);

$filter = new PHP_CodeCoverage_Filter();
$filter->addDirectoryToWhitelist($basePath.'/src/', '.php');

$runner = new PHPUnit_TextUI_TestRunner(null, $filter);
$runner->doRun($masterSuite, $args);

function suite_add_dir($suite, $dir)
{
    foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir), \RecursiveIteratorIterator::LEAVES_ONLY) as $item) {
        foreach (require_tests($item) as $class) {
            $suite->addTest(new PHPUnit_Framework_TestSuite($class));
        }
    }
}

function require_tests($file)
{
    static $cache = array();

    if (!preg_match("/Test\.php$/", $file))
        return array();

    $file = realpath($file);
    if (isset($cache[$file]))
        return $cache[$file];

    $prevClasses = get_declared_classes();
    require $file;
    $nowClasses = get_declared_classes();

    $tests = array_diff($nowClasses, $prevClasses);
    $found = array();
    foreach ($tests as $class) {
        if (preg_match("/Test$/", $class)) {
            $found[] = $class;
        }
    }
    $cache[$file] = $found;

    return $found;
}

/**
 * StreamWrapper::stream_fread is usually called with a minimum buffer size of 8192
 * regardless of what value is passed to fread(). It will then keep reading until
 * the value passed to fread() is satisfied or EOF is reached.
 *
 * This can be overridden by calling stream_set_chunk_size($size), which has a
 * bizarre and undocumented quirk:
 *
 * // Pass $len from fread($h, $len) to StreamWrapper::stream_read($len)
 * stream_set_chunk_size($h, 1);
 *
 * // Pass stream chunk size to StreamWrapper::stream_read($len)
 * stream_set_chunk_size($h, 2);
 *
 * This function just tests that my assumptions about this behaviour remain correct.
 */
function stream_chunk_ensure_quirk()
{
    stream_wrapper_register('streamchunkquirk', 'Defile\Test\BufferTestingStream');

    $h = fopen('streamchunkquirk://yep', 'r');
    fread($h, 1);
    fclose($h);
    if (\Defile\Test\BufferTestingStream::$bufferSize != 8192)
        throw new \UnexpectedValueException();

    $h = fopen('streamchunkquirk://yep', 'r');
    stream_set_chunk_size($h, 1);
    fread($h, 999);
    fclose($h);
    if (\Defile\Test\BufferTestingStream::$bufferSize != 999)
        throw new \UnexpectedValueException();

    $h = fopen('streamchunkquirk://yep', 'r');
    stream_set_chunk_size($h, 2);
    fread($h, 999);
    fclose($h);
    if (\Defile\Test\BufferTestingStream::$bufferSize != 2)
        throw new \UnexpectedValueException(\Defile\Test\BufferTestingStream::$bufferSize);

    stream_wrapper_unregister('streamchunkquirk');
}

