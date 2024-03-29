<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\Bus\Handler;

use AwdStudio\Bus\Handler\ClassHandlerRegistry;
use AwdStudio\Bus\Handler\ParentsAwareClassHandlerRegistry;
use AwdStudio\Bus\HandlerLocator;
use AwdStudio\Bus\Registry\ImplementationParser;
use AwdStudio\Tests\BusTestCase;
use Prophecy\Argument;

/**
 * @coversDefaultClass \AwdStudio\Bus\Handler\ParentsAwareClassHandlerRegistry
 */
final class ParentsAwareHandlersTest extends BusTestCase
{
    /** @var \AwdStudio\Bus\Handler\ParentsAwareClassHandlerRegistry */
    private $instance;

    /** @var \AwdStudio\Bus\Handler\ClassHandlerRegistry|\Prophecy\Prophecy\ObjectProphecy */
    private $handlersRegistryProphesy;

    /** @var \AwdStudio\Bus\Registry\ImplementationParser|\Prophecy\Prophecy\ObjectProphecy */
    private $parserProphesy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handlersRegistryProphesy = $this->prophesize(ClassHandlerRegistry::class);
        $this->parserProphesy = $this->prophesize(ImplementationParser::class);

        $this->instance = new ParentsAwareClassHandlerRegistry(
            $this->handlersRegistryProphesy->reveal(),
            $this->parserProphesy->reveal()
        );
    }

    /**
     * @covers ::__construct
     */
    public function testMustImplementAHandlers(): void
    {
        $this->assertInstanceOf(HandlerLocator::class, $this->instance);
    }

    /**
     * @covers ::__construct
     */
    public function testMustAllowNotToPassReflector(): void
    {
        $this->assertNotNull(new ParentsAwareClassHandlerRegistry($this->handlersRegistryProphesy->reveal()));
    }

    /**
     * @covers ::register
     */
    public function testMustRegisterViaHandlers(): void
    {
        $this->handlersRegistryProphesy
            ->register(Argument::exact('Foo'), Argument::exact('FooHandler'), Argument::type('string'))
            ->shouldBeCalledOnce();

        $this->instance->register('Foo', 'FooHandler');
    }

    /**
     * @covers ::add
     */
    public function testMustAddViaHandlers(): void
    {
        $handler = static function (): void { };

        $this->handlersRegistryProphesy
            ->add(Argument::exact('Foo'), Argument::exact($handler))
            ->shouldBeCalledOnce();

        $this->instance->add('Foo', $handler);
    }

    /**
     * @covers ::has
     */
    public function testMustReturnTrueIfHandlersHasAHandler(): void
    {
        $this->handlersRegistryProphesy
            ->has(Argument::exact('Foo'))
            ->willReturn(true)
            ->shouldBeCalledOnce();

        $this->assertTrue($this->instance->has('Foo'));
    }

    /**
     * @covers ::parse
     */
    public function testMustParseParentsIfHandlersDoesNotHaveAHandler(): void
    {
        $this->handlersRegistryProphesy
            ->has(Argument::exact('Foo'))
            ->willReturn(false)
            ->shouldBeCalledOnce();

        $this->parserProphesy
            ->parse(Argument::exact('Foo'))
            ->willReturn([])
            ->shouldBeCalledOnce();

        $this->instance->has('Foo');
    }

    /**
     * @covers ::has
     */
    public function testMustReturnTrueIfHandlersContainsAHandlerFromOneOfParsedResults(): void
    {
        $this->handlersRegistryProphesy
            ->has(Argument::any())
            ->willReturn(false, true);

        $this->parserProphesy
            ->parse(Argument::exact('Foo'))
            ->willReturn(['IFoo']);

        $this->assertTrue($this->instance->has('Foo'));
    }

    /**
     * @covers ::has
     */
    public function testMustReturnFalseIfThereIsNoOneHandlerEvenForParents(): void
    {
        $this->handlersRegistryProphesy
            ->has(Argument::any())
            ->willReturn(false, false, false);

        $this->parserProphesy
            ->parse(Argument::exact('Foo'))
            ->willReturn(['IFoo', 'IBar']);

        $this->assertFalse($this->instance->has('Foo'));
    }

    /**
     * @covers ::get
     */
    public function testMustAskHandlersFromHandlers(): void
    {
        $this->handlersRegistryProphesy
            ->get(Argument::exact('Foo'))
            ->willYield([])
            ->shouldBeCalledOnce();

        $this->parserProphesy
            ->parse(Argument::exact('Foo'))
            ->willReturn([])
            ->shouldBeCalledOnce();

        \iterator_to_array($this->instance->get('Foo'));
    }

    /**
     * @covers ::get
     */
    public function testMustYieldAllHandlersFromHandlers(): void
    {
        $handler = static function (): void { };

        $this->handlersRegistryProphesy
            ->get(Argument::exact('Foo'))
            ->willYield([$handler]);

        $this->parserProphesy
            ->parse(Argument::exact('Foo'))
            ->willReturn([]);

        $handlers = \iterator_to_array($this->instance->get('Foo'));

        $this->assertContains($handler, $handlers);
    }

    /**
     * @covers ::parse
     */
    public function testMustParseImplementationsToYieldMoreHandlers(): void
    {
        $this->handlersRegistryProphesy
            ->get(Argument::any())
            ->willYield([])
            ->shouldBeCalledTimes(3);

        $this->parserProphesy
            ->parse(Argument::exact('Foo'))
            ->willReturn(['IFoo', 'IBar'])
            ->shouldBeCalledOnce();

        \iterator_to_array($this->instance->get('Foo'));
    }

    /**
     * @covers ::get
     */
    public function testMustReturnNothingIfThereAreNoHandlers(): void
    {
        $this->handlersRegistryProphesy
            ->get(Argument::any())
            ->willYield([]);

        $this->parserProphesy
            ->parse(Argument::exact('Foo'))
            ->willReturn(['IFoo', 'IBar']);

        $this->assertEmpty(\iterator_to_array($this->instance->get('Foo')));
    }
}
