<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer;

use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Node;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Relationship\Relationship;

class ClassDiagram
{
    /**
     * @var Node[]
     */
    private array $nodes;

    /**
     * @var Relationship[]
     */
    private array $relationships = [];

    public function addNode(Node $node): self
    {
        $this->nodes[] = $node;

        Node::sortNodes($this->nodes);

        return $this;
    }

    public function addRelationships(Relationship ...$relationships): self
    {
        $this->relationships = [...$this->relationships, ...$relationships];

        Relationship::sortRelationships($this->relationships);

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
