<?php
declare(strict_types=1);


namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector;


use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Class_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Interface_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Node;

class InheritanceConnector extends Connector
{
    public function connect(array $nodes): void
    {
        $node = $nodes[$this->nodeName];

        foreach ($this->toConnectNodeNames as $toConnectNodeName) {
            $node->extends(
                $nodes[$toConnectNodeName] ?? $this->createDefaultExtendsNode($node, $toConnectNodeName)
            );
        }
    }

    private function createDefaultExtendsNode(Node $extended, string $extendsNodeName): Node
    {
        return match (true) {
            $extended instanceof Interface_ => new Interface_($extendsNodeName),
            default => new Class_($extendsNodeName),
        };
    }
}
