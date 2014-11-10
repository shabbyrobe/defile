<?php
namespace Defile\Test\Unit;

class StreamRegistryTest extends \PHPUnit_Framework_TestCase
{
    function setUp()
    {
        $this->registeredStreams = stream_get_wrappers();
        $this->fs = $this->getMockBuilder('Defile\FileSystem')->getMock();
    }

    function testAdd()
    {
        $registry = new \Defile\StreamRegistry;
        $registry->add('foo', 'bar', $this->fs);
        $this->assertRegisteredStreams(['foo']);
    }

    function testAddMany()
    {
        $registry = new \Defile\StreamRegistry;
        $registry->add('foo', 'bar', $this->fs);
        $registry->add('bar', 'baz', $this->fs);
        $this->assertRegisteredStreams(['foo', 'bar']);
    }

    function tearDown()
    {
        $registered = array_values(array_diff(stream_get_wrappers(), $this->registeredStreams));
        foreach ($registered as $r)
            stream_wrapper_unregister($r);

        if (stream_get_wrappers() != $this->registeredStreams)
            throw new \UnexpectedValueException();
    }

    function assertRegisteredStreams($expected)
    {
        $result = array_values(array_diff(stream_get_wrappers(), $this->registeredStreams));
        $this->assertEquals($expected, $result);
    }
}
