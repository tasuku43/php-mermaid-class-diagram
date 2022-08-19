<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer;

use Exception;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\NodeBuilder;

class ClassDiagramBuilder
{
    public function __construct(private NodeBuilder $nodeBulder)
    {
    }

    /**
     * @param string $path
     * @return ClassDiagram
     * @throws Exception
     */
    public function build(string $path): ClassDiagram
    {
        $classDigagram = new ClassDiagram();

        foreach ($this->nodeBulder->build($path) as $node) {
            $classDigagram->addNode($node)->addRelationships(...$node->relationships());
        }

        return $classDigagram;
    }
}
