<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Relationship;

use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Node;

abstract class Relationship
{
    protected const FORMAT = "%s %s %s: %s";
    public function __construct(protected Node $from, protected Node $to)
    {
    }

    abstract protected function render(): string;
}
