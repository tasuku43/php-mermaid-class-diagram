<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship;

class TraitUsage extends Relationship
{
    public function render(): string
    {
        // Represent trait usage with a simple association arrow and label
        return sprintf(self::FORMAT, $this->from->nodeName(), '-->', $this->to->nodeName(), 'use');
    }
}
