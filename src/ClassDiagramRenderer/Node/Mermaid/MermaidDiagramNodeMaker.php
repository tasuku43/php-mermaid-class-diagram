<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Mermaid;

use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\DiagramNode;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\DiagramNodeMaker;

class MermaidDiagramNodeMaker implements DiagramNodeMaker
{
    public function makeClass(string $name): DiagramNode
    {
        return  new Class_($name);
    }
    public function makeAbstractClass(string $name): DiagramNode
    {
        return new AbstractClass_($name);
    }

    public function makeInterface(string $name): DiagramNode
    {
        return new Interface_($name);
    }
}
