<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Relationship;

class Composition extends Relationship
{
    public function render(): string
    {
        return sprintf(self::FORMAT, $this->from->nodeName(), '*--', $this->to->nodeName(), 'composition');
    }
}
