<?php
namespace Defile;

function dumpx()
{
	call_user_func_array('dump', func_get_args());
	exit;
}

function dump($var, $depth=null, $highlight=null, $return=false)
{
	static $defaultHighlight=null;
	if ($highlight === null) {
		if ($defaultHighlight === null) {
			$defaultHighlight = php_sapi_name()!='cli';
		}
		$highlight = $defaultHighlight;
	}
	if ($depth === null) $depth = 10;
	
	$dump = dump_var($var, $depth);
	
	if ($highlight) {
		$dump = highlight_string("<?php\n".$dump, true);
		$dump = preg_replace('@&lt;\?php<br />@s', '', $dump, 1);
		$dump = '<div style="text-align:left">'.$dump.'</div>';
	}
	
	if ($return) return $dump;
	else echo $dump;
}

function dump_file($dest, $var, $depth=null)
{
	$bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
	$lastCall = null;
	if (isset($bt[0])) {
		if (isset($bt[0]['file']))
			$lastCall .= $bt[0]['file'];
		if (isset($bt[0]['line']))
			$lastCall .= ':'.$bt[0]['line'];
		if ($lastCall) $lastCall .= ' ';
	}
	
	$out = date('[Y-m-d H:i:s]').' '.$lastCall.PHP_EOL.dump($var, $depth, !'highlight', 'return').PHP_EOL.PHP_EOL;
	$dir = dirname($dest);
	if ($dir && @is_writable($dir)) {
		@file_put_contents($dest, $out, FILE_APPEND);
	}
}

function dump_var($var, $depth=null)
{
	if ($depth === null) $depth = 3;

	static $indent = 0;
	static $objects = array();
	static $ocnt = 0;
	static $spaces = 4;

	$out = '';
	$type = gettype($var);
	switch ($type) {
		case 'NULL':
			$out .= 'null';
		break;

		case 'integer':
			$out .= $var;
		break;

		case 'string':
			$out .= "'".addslashes($var)."'";
		break;

		case 'double':
			$out .= $var.'D';
		break;

		case 'boolean':
			$out .= $var ? 'true' : 'false';
		break;

		case 'resource':
			$out .= '[resource: '.get_resource_type($var).']';
		break;

		case 'array':
		case 'object':
			$obj = $type == 'object';
			$name = $obj ? get_class($var) : 'array';
			$hash = $obj ? spl_object_hash($var) : null;

			++ $indent;

			if ($indent >= $depth) {
				$out .= $name.' (...)';
			}
			elseif ($var instanceof \DirectoryIterator) {
				// unboolable classes WTF
				ob_start();
				print_r($var);
				$out .= ob_get_clean(); 
			}
			elseif (!$var) {
				$out .= $name.' ()';
			}
			elseif (isset($objects[$hash])) {
				$out .= $name.'#'.$objects[$hash].' (...)';
			}
			else {
				if ($obj) {
					$fmt = '[%s]';
					$objects[$hash] = ++$ocnt;
					$name .= '#'.$ocnt;
				}
				else $fmt = "'%s'";

				$margin = str_repeat(' ', $indent * $spaces);
				$out .= $name." (\n";
				foreach ((array)$var as $k=>$v) {
					$k = str_replace("\0", ':', trim($k));
					$out .= $margin.sprintf($fmt, $k)." => ".dump_var($v, $depth)."\n";
				}
				$out .= str_repeat(' ', ($indent - 1) * $spaces).")";
			}
			-- $indent;
		break;
	}

	if ($indent == 0) {
		$objects = array();
		$ocnt = 0;
		$out .= "\n";
	}

	return $out;
}

set_error_handler(function($code, $msg, $file, $line, $context) {
    throw new \ErrorException($msg, null, $code, $file, $line);
});

StreamRegistry::instance()->add("foo", "pants", new MemoryFileSystem());

$fs = StreamRegistry::instance()->get('foo', 'pants');

file_put_contents('foo://pants/path', 'test', FILE_APPEND);
var_dump($fs->getNode('/path'));
var_dump(file_get_contents('foo://pants/path'));

