<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer;

use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\AbstractClass_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Class_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Enum_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Interface_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Node;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Composition;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Dependency;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Inheritance;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Realization;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Relationship;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\TraitUsage;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Trait_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\RenderOptions\RenderOptions;

class ClassDiagramDumper
{
    public function __construct(private ClassDiagram $diagram)
    {
    }

    public function toYaml(?RenderOptions $options = null): string
    {
        $options = $options ?? RenderOptions::default();

        [$nodes, $relationships] = $this->extract($options);

        $output = "nodes:\n";
        foreach ($nodes as $node) {
            $output .= "  - type: " . $this->nodeType($node) . "\n";
            $output .= "    name: " . $node->nodeName() . "\n";
        }

        $output .= "relationships:\n";
        foreach ($relationships as $relationship) {
            $output .= "  - type: " . $this->relationshipType($relationship) . "\n";
            $output .= "    from: " . $relationship->from->nodeName() . "\n";
            $output .= "    to: " . $relationship->to->nodeName() . "\n";
        }

        return $output;
    }

    /**
     * @return array{0: array<int, Node>, 1: array<int, Relationship>}
     */
    private function extract(RenderOptions $options): array
    {
        $ref = new \ReflectionClass($this->diagram);

        $nodesProp = $ref->getProperty('nodes');
        $nodesProp->setAccessible(true);
        $nodes = $nodesProp->getValue($this->diagram);

        $relationshipsProp = $ref->getProperty('relationships');
        $relationshipsProp->setAccessible(true);
        $relationships = $relationshipsProp->getValue($this->diagram);

        // Apply the same filtering/sorting policy as render()
        $nodes = $nodes->optimize($options)->sort()->getAll();
        $relationships = $relationships->optimize($options)->sort()->getAll();

        return [$nodes, $relationships];
    }

    private function nodeType(Node $node): string
    {
        return match (true) {
            $node instanceof AbstractClass_ => 'AbstractClass',
            $node instanceof Interface_     => 'Interface',
            $node instanceof Trait_         => 'Trait',
            $node instanceof Enum_          => 'Enum',
            $node instanceof Class_         => 'Class',
            default                         => 'Unknown',
        };
    }

    private function relationshipType(Relationship $relationship): string
    {
        return match (true) {
            $relationship instanceof Inheritance  => 'inheritance',
            $relationship instanceof Realization  => 'realization',
            $relationship instanceof Dependency   => 'dependency',
            $relationship instanceof Composition  => 'composition',
            $relationship instanceof TraitUsage   => 'traitUsage',
            default                               => 'unknown',
        };
    }
}
