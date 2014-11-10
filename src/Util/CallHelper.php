<?php
namespace Defile\Util;

class CallHelper
{
    /**
     * Python-style args/kwargs argument arrays. Creates an indexed
     * argument list to use with reflection::
     * 
     *     class Pants {
     *         function doPants($arg1, $arg2, $arg3=null, $foo=>null) {
     *         }
     *     }
     *     
     * 	   $args = ['arg1', 'arg2', 'foo'=>'bar'];
     *     $rm = (new ReflectionClass('Pants'))->getMethod('doPants');
     *     $return = $rm->invokeArgs(func_get_call_args($rm), $args);
     */
    public static function getCallArgs(\ReflectionFunctionAbstract $rm, $args, $ignoreUnknown=false)
    {
        if (!$args)
            $args = [];
        
        $callArgs = [];
        $inArgs = true;
        
        foreach ($rm->getParameters() as $idx=>$param) {
            $paramFound = false;
            if ($inArgs && $inArgs = isset($args[$idx])) {
                $callArgs[] = $args[$idx];
                $paramFound = true;
                unset($args[$idx]);
            }
            else {
                if (array_key_exists($param->name, $args)) {
                    $paramFound = true;
                    $callArgs[] = $args[$param->name];
                    unset($args[$param->name]);
                }
            }
            
            if (!$paramFound) {
                if ($param->isDefaultValueAvailable()) {
                    $callArgs[] = $param->getDefaultValue();
                }
                else {
                    throw new \UnexpectedValueException("No value for argument {$param->name} for function {$rm->getName()}");
                }
            }
        }
        if ($args && !$ignoreUnknown) {
            throw new \UnexpectedValueException("Unknown keyword arguments: ".implode(", ", array_keys($args)));
        }
        
        return $callArgs;
    }

    /**
     * Calls a function using the python keyword emulation of func_get_call_args()
     */
    public static function call($name, $args, $ignoreUnknown=false)
    {
        if (is_array($name)) {
            $rc = new \ReflectionClass($name[0]);
            $func = $rc->getMethod($name[1]);
        }
        elseif (!$name instanceof \ReflectionFunction) {
            $func = new \ReflectionFunction($name);
        }
        if (!$func instanceof \ReflectionFunctionAbstract) {
            throw new \InvalidArgumentException();
        }

        $callArgs = func_get_call_args($func, $args, $ignoreUnknown);

        return call_user_func_array($name, $callArgs);
    }

    public static function getNamedArgs()
    {
        $bt = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        $frame = $bt[1];
        if ($frame['function'] == '{closure}')
            throw new \LogicException("This can't work inside a closure, sadly");
        
        if (isset($frame['class'])) {
            $rc = new \ReflectionClass($frame['class']);
            $rf = $rc->getMethod($frame['function']);
        }
        else {
            $rf = new \ReflectionFunction($frame['function']);
        }
        $named = [];
        $args = $frame['args'];
        foreach ($rf->getParameters() as $idx=>$param) {
            $name = $param->getName();
            if (!array_key_exists($idx, $args)) {
                if ($param->isDefaultValueAvailable())
                    $named[$name] = $param->getDefaultValue();
                else
                    break;
            }
            else {
                $named[$name] = $args[$idx];
            }
        }
        return $named;
    }
}

