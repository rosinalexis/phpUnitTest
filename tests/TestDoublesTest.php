<?php

use App\ExampleCommand;
use App\ExampleDependency;
use App\ExampleService;
use PHPUnit\Framework\TestCase;

class TestDoublesTest extends TestCase
{
    public function testMock()
    {
        $mock = $this->createMock(ExampleService::class);
        $mock
            ->expects($this->once())
            ->method('doSomething')
            ->with('bar')
            ->willReturn('foo');

        $exampleCommand = new ExampleCommand($mock);

        $this->assertSame('foo', $exampleCommand->execute('bar'));

    }

    public function testReturnTypes()
    {
        $mock = $this->createMock(ExampleService::class);

        $this->assertNull($mock->doSomething('bar'));
    }

    public function testConsecutiveReturns()
    {
        $mock = $this->createMock(ExampleService::class);

        $mock->method('doSomething')
            ->willReturnOnConsecutiveCalls(1, 2, 3);

        foreach ([1, 2] as $value) {

            $this->assertSame($value, $mock->doSomething('bar'));
        }
    }

    public function testExceptionsThrown()
    {
        $mock = $this->createMock(ExampleService::class);
        $mock->method('doSomething')
            ->willThrowException(new RuntimeException());

        $this->expectException(RuntimeException::class);

        $mock->doSomething('bar');
    }

    public function testCallbackReturns()
    {
        $mock = $this->createMock(ExampleService::class);
        $mock->method('doSomething')
            ->willReturnCallback(function ($arg) {
                if ($arg % 2 == 0) {
                    return $arg;
                }

                throw new InvalidArgumentException();
            });

        $this->assertSame(10, $mock->doSomething(10));

        $this->expectException(InvalidArgumentException::class);
        $mock->doSomething(9);
    }

    public function testWithEqualTo()
    {
        $mock = $this->createMock(ExampleService::class);
        $mock->expects($this->once())
            ->method('doSomething')
            ->with($this->equalTo('bar'));

        $mock->doSomething('bar');
    }

    public function testMultipleArgs()
    {
        $mock = $this->createMock(ExampleService::class);

        $mock->expects($this->once())
            ->method('doSomething')
            ->with(
                $this->stringContains('foo'),
                $this->greaterThanOrEqual(100),
                $this->anything()
            );

        $mock->doSomething('foobar', 101, null);

    }


    public function testCallbackArguments()
    {
        $mock = $this->createMock(ExampleService::class);

        $mock->expects($this->once())
            ->method('doSomething')
            ->with($this->callback(function ($object) {

                $this->assertInstanceOf(ExampleDependency::class, $object);
                return $object->exampleMethod() === 'Example string';
            }));

        $mock->doSomething(new ExampleDependency);

    }

    public function testIdenticalTo()
    {
        $dependency = new ExampleDependency();

        $mock = $this->createMock(ExampleService::class);
        $mock->expects(($this->once()))
            ->method('doSomething')
            ->with($this->identicalTo($dependency));

        $mock->doSomething($dependency);

    }

    public function testMockBuilder()
    {
        $mock = $this->getMockBuilder(ExampleService::class)
            ->setConstructorArgs([100, 200])
            ->getMock();

        $mock->method('doSomething')->willReturn('foo');

        $this->assertSame('foo', $mock->doSomething('bar'));
    }

    public function testOnlyMethods()
    {
        $mock = $this->getMockBuilder(ExampleService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['doSomething'])
            ->getMock();

        $mock->method('doSomething')->willReturn('foo');

        $this->assertSame('foo', $mock->nonMockMethod('bar'));
    }


    public function testAddMethods()
    {
        $mock = $this->getMockBuilder(ExampleService::class)
            ->disableOriginalConstructor()
            ->addMethods(['nonExistentMethod'])
            ->getMock();

        $mock->expects($this->once())
            ->method('nonExistentMethod')
            ->with($this->isInstanceOf(ExampleDependency::class))
            ->willReturn('foo');

        $this->assertSame('foo', $mock->nonExistentMethod(new ExampleDependency));

    }

}