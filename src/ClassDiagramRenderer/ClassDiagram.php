<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer;

use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Node;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Nodes;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Dependency;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Composition;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Inheritance;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Realization;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Relationship;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Relationships;

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
        $this->nodes->sort();
        $nodes = $this->nodes->getAllNodes();
        $this->relationships->sort();

        $output = "classDiagram\n";

        foreach ($nodes as $node) {
            $output .= "    " . $node->render() . "\n";
        }

        $output .= "\n";

        foreach ($this->relationships->filter($options)->getAll() as $relationship) {
            $output .= "    " . $relationship->render() . "\n";
        }

        return $output;
    }
}
