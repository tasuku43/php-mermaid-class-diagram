<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node;

interface DiagramNodeMaker
{
    public function makeAbstractClass(string $name): DiagramNode;
    public function makeClass(string $name): DiagramNode;
    public function makeInterface(string $name): DiagramNode;
}
