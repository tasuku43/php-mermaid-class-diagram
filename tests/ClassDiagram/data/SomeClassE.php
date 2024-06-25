<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagram\data;

class SomeClassE
{
    public function __construct(private SomeClassA $a)
    {
        $b = new SomeClassB;
    }

    public function dependAandB(SomeClassA $a): SomeClassC
    {
    }
}
