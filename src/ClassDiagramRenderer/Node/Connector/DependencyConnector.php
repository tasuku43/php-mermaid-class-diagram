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

class DependencyConnector extends Connector
{
    public function connect(Nodes $nodes): void
    {
        $node = $nodes->findByName($this->nodeName);

        foreach ($this->toConnectNodeNames as $toConnectNodeName) {
            $node->depend($nodes->findByName($toConnectNodeName) ?? new Class_($toConnectNodeName));
        }
    }

    public static function parse(
        NodeFinder                            $nodeFinder,
        Node\Stmt\Interface_|Node\Stmt\Class_ $classLike,
        ClassDiagramNode                      $classDiagramNode,
    ): self
    {
        $dependencyNodeNames = [];

        // from method parameters and return types
        foreach ($classLike->getMethods() as $method) {
            foreach ($method->getParams() as $param) {
                // If `visibirity` is not specified, flags is 0
                if ($param->type instanceof Name && $param->flags === 0) {
                    $parts                 = $param->type->getParts();
                    $dependencyNodeNames[] = end($parts);
                }
            }

            if ($method->returnType instanceof Name) {
                $parts                 = $method->returnType->getParts();
                $dependencyNodeNames[] = end($parts);
            }
        }

        $newStmts = $nodeFinder->findInstanceOf($classLike, Node\Expr\New_::class);
        foreach ($newStmts as $newStmt) {
            assert($newStmt instanceof Node\Expr\New_);
            if ($newStmt->class instanceof Name) {
                $parts                 = $newStmt->class->getParts();
                $dependencyNodeNames[] = end($parts);
            }
        }

        // remove duplicates
        $dependencyNodeNames = array_unique($dependencyNodeNames);

        return new DependencyConnector($classDiagramNode->nodeName(), $dependencyNodeNames);
    }
}
