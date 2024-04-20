<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector;

use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Nodes;

abstract class Connector
{
    /**
     * @param string   $nodeName
     * @param string[] $toConnectNodeNames
     */
    public function __construct(protected string $nodeName, protected array $toConnectNodeNames)
    {
    }

    abstract public function connect(Nodes $nodes): void;
}
