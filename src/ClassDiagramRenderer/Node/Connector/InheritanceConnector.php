<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector;

use PhpParser\Node;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Class_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Interface_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Node as ClassDiagramNode;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Nodes;

class InheritanceConnector extends Connector
{
    public function connect(Nodes $nodes): void
    {
        $node = $nodes->findByName($this->nodeName);

        foreach ($this->toConnectNodeNames as $toConnectNodeName) {
            $node->extends(
                $nodes->findByName($toConnectNodeName) ?? $this->createDefaultExtendsNode($node, $toConnectNodeName)
            );
        }
    }

    private function createDefaultExtendsNode(ClassDiagramNode $extended, string $extendsNodeName): ClassDiagramNode
    {
        return match (true) {
            $extended instanceof Interface_ => new Interface_($extendsNodeName),
            default => new Class_($extendsNodeName),
        };
    }

    public static function parse(
        Node\Stmt\Interface_|Node\Stmt\Class_|Node\Stmt\Enum_ $classLike,
        ClassDiagramNode                      $classDiagramNode,
    ): self
    {
        $extendsNodeNames = [];

        // Skip inheritance check for enums as they don't have extends property
        if (!($classLike instanceof Node\Stmt\Enum_) && property_exists($classLike, 'extends') && $classLike->extends !== null) {
            $extendsNodeNames = is_array($classLike->extends)
                ? array_map(function (Node\Name $name) {
                    return (string)$name->getLast();
                }, $classLike->extends)
                : [(string)$classLike->extends->getLast()];
        }

        return new InheritanceConnector($classDiagramNode->nodeName(), $extendsNodeNames);
    }
}
