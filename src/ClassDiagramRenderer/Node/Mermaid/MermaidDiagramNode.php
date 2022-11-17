<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Mermaid;

use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\DiagramNode;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\DiagramNodeRenderSupoert;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Relationship\Composition;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Relationship\Inheritance;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Relationship\Realization;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Relationship\Relationship;

abstract class MermaidDiagramNode implements DiagramNode
{
    use DiagramNodeRenderSupoert;

    /**
     * @return Relationship[]
     */
    public function relationships(): array
    {
        return [
            ...array_map(fn(MermaidDiagramNode $extendsNode)    => new Inheritance($this, $extendsNode), $this->extends),
            ...array_map(fn(MermaidDiagramNode $implementsNode) => new Realization($this, $implementsNode), $this->implements),
            ...array_map(fn(MermaidDiagramNode $propertyNode) => new Composition($this, $propertyNode), $this->properties),
        ];
    }
}
