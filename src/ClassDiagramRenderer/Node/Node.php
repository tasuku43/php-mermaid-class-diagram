<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node;

use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Composition;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Dependency;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Inheritance;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Realization;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Relationship;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\TraitUsage;

abstract class Node
{
    protected Nodes $extends;
    protected Nodes $implements;
    protected Nodes $properties;
    protected Nodes $depends;
    protected Nodes $traits;
    /** @var array<Relationship> */
    protected array $extraRelationships;

    public function __construct(protected string $name)
    {
        $this->extends    = Nodes::empty();
        $this->implements = Nodes::empty();
        $this->properties = Nodes::empty();
        $this->depends    = Nodes::empty();
        $this->traits     = Nodes::empty();
        $this->extraRelationships = [];
    }

    abstract public function render(): string;

    public function extends(Node $node): void
    {
        $this->extends->add($node);
    }

    public function implements(Node $node): void
    {
        $this->implements->add($node);
    }

    public function composition(Node $node): void
    {
        $this->properties->add($node);
    }

    public function depend(Node $node): void
    {
        $this->depends->add($node);
    }

    public function useTrait(Node $trait): void
    {
        $this->traits->add($trait);
    }

    public function nodeName(): string
    {
        return $this->name;
    }

    /**
     * @return Relationship[]
     */
    public function relationships(): array
    {
        $extends    = $this->extends->getAllNodes();
        $implements = $this->implements->getAllNodes();
        $properties = $this->properties->getAllNodes();
        $depends    = array_filter($this->depends->getAllNodes(), function (string $key) use ($extends, $implements, $properties) {
            return !array_key_exists($key, $properties) 
                && !array_key_exists($key, $extends) 
                && !array_key_exists($key, $implements) 
                && $key !== $this->nodeName();
        }, ARRAY_FILTER_USE_KEY);

        return [
            ...array_values(array_map(fn(Node $extendsNode) => new Inheritance($this, $extendsNode), $extends)),
            ...array_values(array_map(fn(Node $implementsNode) => new Realization($this, $implementsNode), $implements)),
            ...array_values(array_map(fn(Node $propertyNode) => new Composition($this, $propertyNode), $properties)),
            ...array_values(array_map(fn(Node $dependNode) => new Dependency($this, $dependNode), $depends)),
            ...$this->extraRelationships,
        ];
    }

    public static function sortNodes(array &$nodes): void
    {
        usort($nodes, function (Node $a, Node $b) {
            return strcmp($a->nodeName(), $b->nodeName());
        });
    }
}
