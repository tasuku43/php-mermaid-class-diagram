<?php
declare(strict_types=1);


namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector;


use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Class_;

class CompositionConnector extends Connector
{
    public function connect(array $nodes): void
    {
        $node = $nodes[$this->nodeName];
        foreach ($this->toConnectNodeNames as $connectedNodeName) {
            $node->composition($nodes[$connectedNodeName] ?? new Class_($connectedNodeName));
        }
    }
}
