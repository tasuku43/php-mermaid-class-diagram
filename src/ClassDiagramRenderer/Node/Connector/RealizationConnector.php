<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector;

use PhpParser\Node;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Interface_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Node as ClassDiagramNode;

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

    public static function parse(
        Node\Stmt\Interface_|Node\Stmt\Class_ $classLike,
        ClassDiagramNode                      $classDiagramNode,
    ): self
    {
        $implementsNodeNames = [];

        if (property_exists($classLike, 'implements') && $classLike->implements !== []) {
            $implementsNodeNames = array_map(function (Node\Name $name) {
                return (string)$name->getLast();
            }, $classLike->implements);
        }

        return new RealizationConnector($classDiagramNode->nodeName(), $implementsNodeNames);
    }
}
