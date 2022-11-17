<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node;

use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Mermaid\MermaidDiagramNode;

trait DiagramNodeRenderSupoert
{
    /** @var MermaidDiagramNode[] */
    protected array $extends = [];

    /** @var MermaidDiagramNode[] */
    protected array $implements = [];

    /** @var MermaidDiagramNode[] */
    protected array $properties = [];

    public function __construct(protected string $name)
    {
    }

    abstract public function render(): string;

    public function extends(MermaidDiagramNode $node): void
    {
        $this->extends[] = $node;
    }

    public function implements(MermaidDiagramNode $node): void
    {
        $this->implements[] = $node;
    }

    public function composition(MermaidDiagramNode $node): void
    {
        $this->properties[] = $node;
    }

    public function nodeName(): string
    {
        return $this->name;
    }
}
