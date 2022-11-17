<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Mermaid;

use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Node;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\NodeRenderSupoert;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Relationship\Composition;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Relationship\Inheritance;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Relationship\Realization;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Relationship\Relationship;

abstract class MermaidNode implements Node
{
    use NodeRenderSupoert;

    /**
     * @return Relationship[]
     */
    public function relationships(): array
    {
        return [
            ...array_map(fn(MermaidNode $extendsNode)    => new Inheritance($this, $extendsNode), $this->extends),
            ...array_map(fn(MermaidNode $implementsNode) => new Realization($this, $implementsNode), $this->implements),
            ...array_map(fn(MermaidNode $propertyNode) => new Composition($this, $propertyNode), $this->properties),
        ];
    }
}
