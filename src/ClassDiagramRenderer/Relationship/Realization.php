<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Relationship;

class Realization extends Relationship
{
    protected function arrow(): string
    {
        return '..|>';
    }
}
