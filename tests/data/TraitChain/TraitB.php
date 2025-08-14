<?php
declare(strict_types=1);

namespace TraitChain;

use Tasuku43\Tests\MermaidClassDiagram\data\TraitChain\DepClass;
use Tasuku43\Tests\MermaidClassDiagram\data\TraitChain\DepInterface;

trait TraitB
{
    private DepClass $dep;

    public function doSomething(DepInterface $iface): void
    {
        // noop in tests
    }
}

