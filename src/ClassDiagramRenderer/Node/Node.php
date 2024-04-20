<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node;

use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Composition;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Inheritance;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Realization;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Relationship;

abstract class Node
{
    /** @var Node[] */
    protected array $extends = [];

    /** @var Node[] */
    protected array $implements = [];

    /** @var Node[] */
    protected array $properties = [];

    public function __construct(protected string $name)
    {
    }

    abstract public function render(): string;

    public function extends(Node $node): void
    {
        $this->extends[] = $node;
        self::sortNodes($this->extends);
    }

    public function implements(Node $node): void
    {
        $this->implements[] = $node;
        self::sortNodes($this->implements);
    }

    public function composition(Node $node): void
    {
        $this->properties[] = $node;
        self::sortNodes($this->properties);
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
        return [
            ...array_map(fn(Node $extendsNode)    => new Inheritance($this, $extendsNode), $this->extends),
            ...array_map(fn(Node $implementsNode) => new Realization($this, $implementsNode), $this->implements),
            ...array_map(fn(Node $propertyNode) => new Composition($this, $propertyNode), $this->properties),
        ];
    }

    public static function sortNodes(array &$nodes): void
    {
        usort($nodes, function (Node $a, Node $b) {
            return strcmp($a->nodeName(), $b->nodeName());
        });
    }
}
