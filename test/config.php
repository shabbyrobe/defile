<?php
namespace
{
    function autoload_namespace($prefix, $path, $options=array())
    {
        $defaults = array(
            'prepend'=>false,      // stick the autoloader up the top of the stack rather than on the bottom
            'suffix'=>'.php',      // attached to the last namespace segment to determine the class file name
            'stripPrefix'=>true,   // remove the prefix from the start of the class name before generating path
            'separator'=>'\\',     // namespace separator (switch to _ for 5.2 style)
        );
        $options = array_merge($defaults, $options);
        $prefix = trim($prefix, $options['separator']);
        
        spl_autoload_register(
            function($class) use ($prefix, $path, $options) {
                static $prefixLen = null;
                if ($prefixLen === null) {
                    $prefixLen = strlen($prefix);
                }
                
                if (strpos($class, $prefix.$options['separator'])===0 || $class == $prefix) {
                    $toSplit = $options['stripPrefix']  ? substr($class, $prefixLen) : $class;
                    $file = str_replace('../', '', $path.'/'.str_replace($options['separator'], '/', $toSplit)).$options['suffix'];

                    if (file_exists($file))
                        require $file;
                }
            },
            null,
            $options['prepend']
        );
    }
}

namespace Defile\Test
{
    class TestCase extends \PHPUnit_Framework_TestCase
    {
        function assertWithin($within, $a, $b, $message='')
        {
            $diff = abs($a - $b);
            self::assertThat(
                $diff <= $within, 
                self::isTrue(), 
                $message ?: "Expected $a to be within $within of $b, was $diff"
            );
        }
    }

    class PhpErrorHandlingTestCase extends TestCase
    {
        public function setUp()
        {
            $this->errors = [];
            if (!THROW_ON_ERROR) {
                set_error_handler(function() {
                    $args = func_get_args();
                    $this->errors[] = [
                        'code'=>isset($args[0]) ? $args[0] : null,
                        'message'=>isset($args[1]) ? $args[1] : null,
                        'file'=>isset($args[2]) ? $args[2] : null,
                        'line'=>isset($args[3]) ? $args[3] : null,
                        'stack'=>debug_backtrace(),
                    ];
                });
            }
        }

        public function tearDown()
        {
            if ($this->errors) {
                $out = $this->dumpErrors();
                throw new \LogicException("Unchecked errors found: ".$out);
            }
        }

        public function dumpErrors()
        {
            $errors = $this->errors;
            foreach ($errors as &$error)
                unset($error['stack']);
            ob_start();
            var_dump($errors);
            return ob_get_clean();
        }

        public function assertPhpError($errorTypes, $message=null, $regexp=false)
        {
            if (!is_array($errorTypes))
                $errorTypes = [$errorTypes];

            $count = count($this->errors);
            if ($count !== 1) {
                $this->assertTrue(
                    false, 
                    "Assert single error failed: ".count($this->errors)." error(s) found: ".$this->dumpErrors()
                );
            }
            $error = $this->errors[0];
            $this->errors = [];
            
            $this->checkPhpError($error, $errorTypes, $message, $regexp);
        }

        public function assertPhpErrors($expected)
        {
            $errors = $this->errors;
            $this->errors = [];

            $this->assertEquals(count($expected), count($errors));
            foreach ($errors as $i=>$error) {
                $exp = $expected[$i];
                $this->checkPhpError(
                    $error, $exp[0], 
                    isset($exp[1]) ? $exp[1] : null,
                    isset($exp[2]) && $exp[2]
                );
            }
        }

        protected function checkPhpError($error, $errorTypes, $message, $regexp)
        {
            $this->assertTrue($error == true);

            $found = false;
            $names = [];
            foreach ($errorTypes as $e) {
                // fill this in as needed
                switch ($e) {
                    case E_WARNING: $names[] = "E_WARNING"; break;
                    case E_USER_WARNING: $names[] = "E_USER_WARNING"; break;
                    default: $names[] = "UNKNOWN({$e})"; break; 
                }
                if ($e == $error['code']) {
                    $found = true;
                }
            }
            
            $this->assertTrue($found, "Failed asserting that any error was raised: ".implode(', ', $names));
            if ($message !== null) {
                if ($regexp)
                    $this->assertRegexp($message, $error['message']);
                else
                    $this->assertEquals($message, $error['message']);
            }
        }

        public function assertWarning($message=null)
        {
            $this->assertPhpError([E_USER_WARNING, E_WARNING], $message, false);
        }

        public function assertWarningRegexp($message=null)
        {
            $this->assertPhpError([E_USER_WARNING, E_WARNING], $message, true);
        }
    }

    class BufferTestingStream
    {
        static $bufferSize = null;

        public function stream_set_option($option, $arg1, $arg2)
        {
            return true;
        }

        public function stream_open($path, $mode, $options, &$openedPath)
        {
            return true;
        }

        public function stream_eof()
        {
            return true;
        }

        public function stream_read($count)
        {
            static::$bufferSize = $count;
            return true;
        }

        public function stream_close()
        {}
    }
}
