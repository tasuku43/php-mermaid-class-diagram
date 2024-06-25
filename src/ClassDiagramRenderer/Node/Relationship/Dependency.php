<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship;

class Dependency extends Relationship
{
    public function render(): string
    {
        return sprintf(self::FORMAT, $this->to->nodeName(), '<..', $this->from->nodeName(), 'dependency');
    }
}
