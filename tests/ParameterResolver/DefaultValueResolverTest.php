<?php

namespace Invoker\Test\ParameterResolver;

use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\Reflection\CallableReflection;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class DefaultValueResolverTest extends TestCase implements ContainerInterface
{
    protected array $objects = array();
    protected DefaultValueResolver $sut;

    public function setUp()
    {
        parent::setUp();
        $this->sut = new DefaultValueResolver();
    }

    /**
     * @test
     */
    public function should_resolve_complex_parameter_type() {
        $callableReflection = CallableReflection::create(function(A $a = null) {
            return $a;
        });
        $this->objects["Invoker\Test\ParameterResolver\A"] = new A;
        $result = $this->sut->getParameters($callableReflection, array($this), array());
        $this->assertInstanceOf(A::class, $result[0]);
    }

    /**
     * @test
     */
    public function should_resolve_simple_parameter_type_with_default_value() {
        $callableReflection = CallableReflection::create(function($a = null) {
            return $a;
        });
        $result = $this->sut->getParameters($callableReflection, array(), array());
        $this->assertEquals([null], $result);
    }

    public function get($id)
    {
        return $this->objects[$id];
    }

    public function has($id)
    {
        return array_key_exists($id, $this->objects);
    }
}

class A
{
}