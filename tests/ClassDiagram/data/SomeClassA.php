<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagram\data;

class SomeClassA extends SomeAbstractClass
{
    private SomeClassB $someClassB;

    public function __construct(private SomeClassC $someClassC, SomeClassD $someClassD, private int $int)
    {
    }

    public static function getInstance(): self
    {
        return new self(new SomeClassC(), new SomeClassD(), 1);
    }
}
