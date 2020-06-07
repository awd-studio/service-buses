<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\Bus\Registry;

use AwdStudio\Bus\Registry\ReflectionImplementationParser;
use AwdStudio\Tests\BusTestCase;

interface IFoo {}
interface IBar {}
interface IBaz {}

abstract class Foo implements IFoo {}
abstract class Bar extends Foo implements IBar {}
final class Baz extends Bar implements IBaz {}

/**
 * @coversDefaultClass \AwdStudio\Bus\Registry\ReflectionImplementationParser
 */
final class ReflectionHandlerRegistryTest extends BusTestCase
{
    /** @var \AwdStudio\Bus\Registry\ReflectionImplementationParser */
    private $instance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = new ReflectionImplementationParser();
    }

    public function testMustProvidePublicConstructor(): void
    {
        $this->assertNotNull($this->instance);
    }

    /**
     * @covers ::parse
     */
    public function testMustReturnAnEmptyArrayWhenThereAreNoParents(): void
    {
        $this->assertEmpty($this->instance->parse(IFoo::class));
    }

    /**
     * @covers ::parse
     */
    public function testMustResolveAllParensForAnObject(): void
    {
        $implementations = $this->instance->parse(Baz::class);

        $this->assertCount(5, $implementations);
        $this->assertContains(IFoo::class, $implementations);
        $this->assertContains(IBar::class, $implementations);
        $this->assertContains(IBaz::class, $implementations);
        $this->assertContains(Foo::class, $implementations);
        $this->assertContains(Bar::class, $implementations);
    }
}
