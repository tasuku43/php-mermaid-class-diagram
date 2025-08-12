<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\RenderOptions\RenderOptions;

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

    public function sort(): self
    {
        // Keys are node names; sort by key keeps deterministic order
        ksort($this->nodes);

        return $this;
    }

    public function filter(RenderOptions $options): self
    {
        $filtered = new self();
        foreach ($this->nodes as $node) {
            if (!$options->includeTraits && $node instanceof Trait_) {
                continue;
            }
            $filtered->add($node);
        }

        return $filtered;
    }

    /**
     * @return Node[]
     */
    public function getAll(): array
    {
        return $this->nodes;
    }
}
