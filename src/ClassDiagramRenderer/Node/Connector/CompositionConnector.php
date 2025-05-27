<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeFinder;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Class_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Node as ClassDiagramNode;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Nodes;

class CompositionConnector extends Connector
{
    public function connect(Nodes $nodes): void
    {
        $node = $nodes->findByName($this->nodeName);

        foreach ($this->toConnectNodeNames as $toConnectNodeName) {
            $node->composition($nodes->findByName($toConnectNodeName) ?? new Class_($toConnectNodeName));
        }
    }

    public static function parse(
        NodeFinder $nodeFinder,
        Node\Stmt\Interface_|Node\Stmt\Class_|Node\Stmt\Enum_ $classLike,
        ClassDiagramNode                      $classDiagramNode,
    ): self
    {
        $propertieNodeNames = [];

        // from constructor
        $construct = $nodeFinder->findFirst($classLike, function (Node $node) {
            return $node instanceof ClassMethod && (string)$node->name === '__construct';
        });
        if ($construct !== null) {
            assert($construct instanceof ClassMethod);
            foreach (array_filter($construct->getParams(), fn(Node\Param $param) => $param->type instanceof Name) as $param) {
                assert($param instanceof Node\Param);

                // If `visibirity` is not specified, flags is 0
                if ($param->flags !== 0) {
                    $propertieNodeNames = array_merge(
                        $propertieNodeNames,
                        [$param->type->getLast()]
                    );
                }
            }
        }

        // from properties
        $propertieNodeNames = array_merge(array_map(function (Property $property) {
            return $property->type->getLast();
        }, array_filter($classLike->getProperties(),
                fn(Property $property) => $property->type instanceof FullyQualified)
        ), $propertieNodeNames);

        return new CompositionConnector($classDiagramNode->nodeName(), $propertieNodeNames);
    }
}
