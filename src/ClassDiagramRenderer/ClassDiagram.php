<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer;

use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Node;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Relationship;

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

        return $this;
    }

    public function addRelationships(Relationship ...$relationships): self
    {
        $this->relationships = [...$this->relationships, ...$relationships];

        return $this;
    }

    public function render(): string
    {
        Node::sortNodes($this->nodes);
        Relationship::sortRelationships($this->relationships);

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
