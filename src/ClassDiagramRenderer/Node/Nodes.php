<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node;

class Nodes
{
    /**
     * @var Node[]
     */
    private array $nodes;

    public function __construct()
    {
        $this->nodes = [];
    }

    public static function empty(): self
    {
        return new self();
    }

    public function add(Node $node): self
    {
        $this->nodes[$node->nodeName()] = $node;
        return $this;
    }

    public function findByName(string $nodeName): ?Node
    {
        return $this->nodes[$nodeName] ?? null;
    }

    public function sort(): void
    {
        // Keys are node names; sort by key keeps deterministic order
        ksort($this->nodes);
    }

    /**
     * @return Node[]
     */
    public function getAllNodes(): array
    {
        return $this->nodes;
    }
}
