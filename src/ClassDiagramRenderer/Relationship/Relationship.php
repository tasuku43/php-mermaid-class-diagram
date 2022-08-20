<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Relationship;

use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Node;

abstract class Relationship
{
    private string $description;

    public function __construct(private Node $from, private Node $to, string $description = null)
    {
        $this->description = $description ?? $this->description();
    }

    public function render(): string
    {
        $format = "%s %s %s: %s";
        return sprintf($format, $this->from->nodeName(), $this->arrow(), $this->to->nodeName(), $this->description);
    }

    abstract protected function arrow(): string;
    abstract protected function description(): string;
}
