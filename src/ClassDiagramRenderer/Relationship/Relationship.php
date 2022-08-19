<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Relationship;

use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Node;

abstract class Relationship
{
    public function __construct(private Node $from, private Node $to)
    {
    }

    public function render(): string
    {
        return sprintf("%s %s %s", $this->from->nodeName(), $this->arrow(), $this->to->nodeName());
    }

    abstract protected function arrow(): string;
}
