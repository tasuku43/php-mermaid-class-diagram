<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node;

use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Mermaid\MermaidDiagramNode;

interface DiagramNode
{
    public function render(): string;
    public function extends(MermaidDiagramNode $node): void;
    public function implements(MermaidDiagramNode $node): void;
    public function composition(MermaidDiagramNode $node): void;
    public function nodeName(): string;
    public function relationships(): array;
}
