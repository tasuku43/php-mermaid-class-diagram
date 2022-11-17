<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer;

use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\DiagramNode;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Relationship\Relationship;

class ClassDiagram
{
    /**
     * @var DiagramNode[]
     */
    private array $nodes;

    /**
     * @var Relationship[]
     */
    private array $relationships = [];

    public function getNodes(): array
    {
        return $this->nodes;
    }

    public function getRelationships(): array
    {
        return $this->relationships;
    }

    public function addNode(DiagramNode $node): self
    {
        $this->nodes[] = $node;

        return $this;
    }

    public function addRelationships(Relationship ...$relationships): self
    {
        $this->relationships = array_merge($this->relationships, $relationships);

        return $this;
    }

    public function render(): string
    {
        $output = "classDiagram\n";

        foreach ($this->nodes as $node) {
            $output .= "    " . $node->render() . "\n";
        }

        $output .= "\n";

        foreach ($this->relationships as $relationship) {
            $output .= "    " . $relationship->render() . "\n";
        }

        return $output;
    }
}
