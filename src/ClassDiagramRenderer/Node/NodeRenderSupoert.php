<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node;

use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Mermaid\MermaidNode;

trait NodeRenderSupoert
{
    /** @var MermaidNode[] */
    protected array $extends = [];

    /** @var MermaidNode[] */
    protected array $implements = [];

    /** @var MermaidNode[] */
    protected array $properties = [];

    public function __construct(protected string $name)
    {
    }

    abstract public function render(): string;

    public function extends(MermaidNode $node): void
    {
        $this->extends[] = $node;
    }

    public function implements(MermaidNode $node): void
    {
        $this->implements[] = $node;
    }

    public function composition(MermaidNode $node): void
    {
        $this->properties[] = $node;
    }

    public function nodeName(): string
    {
        return $this->name;
    }
}
