<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Relationship;

class Composition extends Relationship
{
    protected function arrow(): string
    {
        return '*--';
    }

    protected function description(): string
    {
        return 'composition';
    }
}
