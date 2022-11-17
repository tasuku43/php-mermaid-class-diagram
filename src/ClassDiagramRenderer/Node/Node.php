<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node;

use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Mermaid\MermaidNode;

interface Node
{
    public function render(): string;
    public function extends(MermaidNode $node): void;
    public function implements(MermaidNode $node): void;
    public function composition(MermaidNode $node): void;
    public function nodeName(): string;
    public function relationships(): array;
}
