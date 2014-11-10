<?php
namespace Defile\Util;

class Object
{
	/**
	 * This supports Python's style of args followed by keyword args:
	 * YourClass::construct([$first, $second, 'fourth'=>$fourth, 'third'=>$third]);
	 * See __construct arguments and func_get_call_args
	 */
	public static function construct($args=[])
	{
		$rc = new \ReflectionClass(get_called_class());
		return $rc->newInstanceArgs(CallHelper::getCallArgs($rc->getConstructor(), $args));
	}

	/**
	 * This supports Python's style of args followed by keyword args:
	 * $yourInstance->call('yourMethod', [$first, $second, 'fourth'=>$fourth, 'third'=>$third]);
	 */
	public function call($method, $args=[])
	{
		if (!$this->class)
			$this->class = new \ReflectionClass($this);

		$method = $this->class->getMethod($method);
		return $method->invokeArgs($this, func_get_call_args($method, $args));
	}

	/**
	 * This supports Python's style of args followed by keyword args:
	 * $yourInstance->call('yourMethod', [$first, $second, 'fourth'=>$fourth, 'third'=>$third]);
	 */
	public static function callStatic($method, $args=[])
	{
		$class = new \ReflectionClass(get_called_class());
		$method = $class->getMethod($method);
		return $method->invokeArgs(null, func_get_call_args($method, $args));
	}

    public function __get($name)
    {
        if ($this->hasPublicProperty($name))
            return null;
        else
            throw new \BadMethodCallException("Unknown property $name on class ".get_class($this));
    }

    public function __set($name, $value)
    {
        if ($this->hasPublicProperty($name))
            $this->$name = $value;
        else
            throw new \BadMethodCallException("Unknown property $name on class ".get_class($this));
    }

    public function __isset($name)
    {
        if ($this->hasPublicProperty($name))
            return false;
        else
            throw new \BadMethodCallException("Unknown property $name on class ".get_class($this));
    }

    public function __unset($name)
    {
        if (!$this->hasPublicProperty($name))
            throw new \BadMethodCallException("Unknown property $name on class ".get_class($this));
    }

    private function hasPublicProperty($key)
    {
        // property_exists returns true for private and protected properties, hence this:
        $rc = new \ReflectionClass($this);
        if ($rc->hasProperty($key)) {
            $rp = $rc->getProperty($key);	
            return $rp->isPublic();
        }
        else {
            return false;
        }
    }
}

