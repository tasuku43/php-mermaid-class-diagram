<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector;

use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Interface_;

class RealizationConnector extends Connector
{
    public function connect(array $nodes): void
    {
        $node = $nodes[$this->nodeName];

        foreach ($this->toConnectNodeNames as $toConnectNodeName) {
            $node->implements(
                $nodes[$toConnectNodeName] ?? new Interface_($toConnectNodeName)
            );
        }
    }
}
