<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector;

use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Node;

abstract class Connector
{
    /**
     * @param string   $nodeName
     * @param string[] $toConnectNodeNames
     */
    public function __construct(protected string $nodeName, protected array $toConnectNodeNames)
    {
    }

    /**
     * @param Node[] $nodes
     */
    abstract public function connect(array $nodes): void;
}
