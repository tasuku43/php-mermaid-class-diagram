<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer;

use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Node;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Nodes;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Relationship;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Relationships;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\RenderOptions\RenderOptions;

class ClassDiagram
{
    private Nodes $nodes;

    private Relationships $relationships;

    public function __construct()
    {
        $this->nodes = Nodes::empty();
        $this->relationships = Relationships::empty();
    }

    public function addNode(Node $node): self
    {
        $this->nodes->add($node);

        return $this;
    }

    public function addRelationships(Relationship ...$relationships): self
    {
        foreach ($relationships as $relationship) {
            $this->relationships->add($relationship);
        }

        return $this;
    }

    public function render(RenderOptions $options = null): string
    {
        $nodes        = $this->nodes->filter($options)->sort()->getAll();
        $relationships = $this->relationships->filter($options)->sort()->getAll();

        $output = "classDiagram\n";

        foreach ($nodes as $node) {
            $output .= "    " . $node->render() . "\n";
        }

        $output .= "\n";

        foreach ($relationships as $relationship) {
            $output .= "    " . $relationship->render() . "\n";
        }

        return $output;
    }
}
