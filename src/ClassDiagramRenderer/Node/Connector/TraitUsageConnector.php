<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector;

use PhpParser\Node;
use PhpParser\NodeFinder;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Nodes;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Node as ClassDiagramNode;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Trait_ as DiagramTrait;

class TraitUsageConnector extends Connector
{
    public function connect(Nodes $nodes): void
    {
        $usingNode = $nodes->findByName($this->nodeName);
        if ($usingNode === null) {
            return;
        }

        foreach ($this->toConnectNodeNames as $traitName) {
            $traitNode = $nodes->findByName($traitName) ?? new DiagramTrait($traitName);
            $usingNode->useTrait($traitNode);
        }
    }

    public static function parse(
        NodeFinder $nodeFinder,
        Node\Stmt\Interface_|Node\Stmt\Class_|Node\Stmt\Enum_|Node\Stmt\Trait_ $classLike,
        ClassDiagramNode $classDiagramNode,
    ): self {
        $traitNames = [];

        $traitUses = $nodeFinder->findInstanceOf($classLike, Node\Stmt\TraitUse::class);
        foreach ($traitUses as $traitUse) {
            assert($traitUse instanceof Node\Stmt\TraitUse);
            foreach ($traitUse->traits as $name) {
                $traitNames[] = (string)$name->getLast();
            }
        }

        $traitNames = array_values(array_unique($traitNames));

        return new self($classDiagramNode->nodeName(), $traitNames);
    }
}
