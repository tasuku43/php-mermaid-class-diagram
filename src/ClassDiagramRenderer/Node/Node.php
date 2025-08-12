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

    // Intentionally left without public API; trait aggregation is done in relationships()

    public function nodeName(): string
    {
        return $this->name;
    }

    /**
     * @return Relationship[]
     */
    public function relationships(): array
    {
        $extends    = $this->extends->getAll();
        $implements = $this->implements->getAll();
        $ownProperties = $this->properties->getAll();
        $ownDepends    = $this->depends->getAll();

        // Collect trait-derived relations (no mutation of own collections)
        $traitCompositions = [];
        $traitDependencies = [];
        $visitedTraits = [];
        foreach ($this->traits->getAll() as $traitNode) {
            $this->collectTraitRelations($traitNode, $visitedTraits, $traitCompositions, $traitDependencies);
        }

        // Final sets (properties win over dependencies)
        $finalProperties = $ownProperties + $traitCompositions; // keep own over trait

        $depsUnion = $ownDepends + $traitDependencies; // keep own over trait
        $finalDepends = array_filter($depsUnion, function (string $key) use ($extends, $implements, $finalProperties) {
            return !array_key_exists($key, $finalProperties)
                && !array_key_exists($key, $extends)
                && !array_key_exists($key, $implements)
                && $key !== $this->nodeName();
        }, ARRAY_FILTER_USE_KEY);

        return [
            ...array_values(array_map(fn(Node $extendsNode) => new Inheritance($this, $extendsNode), $extends)),
            ...array_values(array_map(fn(Node $implementsNode) => new Realization($this, $implementsNode), $implements)),
            ...array_values(array_map(fn(Node $propertyNode) => new Composition($this, $propertyNode), $finalProperties)),
            ...array_values(array_map(fn(Node $dependNode) => new Dependency($this, $dependNode), $finalDepends)),
            ...$this->extraRelationships,
        ];
    }

    /**
     * Recursively collect composition/dependency from the given trait and nested traits.
     *
     * @param Node  $traitNode
     * @param array $visited           visited trait names
     * @param array $compositionsOut   [name => Node]
     * @param array $dependenciesOut   [name => Node]
     */
    private function collectTraitRelations(Node $traitNode, array &$visited, array &$compositionsOut, array &$dependenciesOut): void
    {
        $traitName = $traitNode->nodeName();
        if (isset($visited[$traitName])) {
            return;
        }
        $visited[$traitName] = true;

        // Direct compositions and dependencies declared in the trait
        foreach ($traitNode->properties->getAll() as $name => $node) {
            $compositionsOut[$name] = $node;
        }
        foreach ($traitNode->depends->getAll() as $name => $node) {
            $dependenciesOut[$name] = $node;
        }

        // Nested trait uses
        foreach ($traitNode->traits->getAll() as $nestedTrait) {
            $this->collectTraitRelations($nestedTrait, $visited, $compositionsOut, $dependenciesOut);
        }
    }
}
