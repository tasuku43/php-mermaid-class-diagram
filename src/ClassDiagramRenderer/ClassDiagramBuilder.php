<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer;

use Exception;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\DiagramNodeParser;

class ClassDiagramBuilder
{
    public function __construct(private DiagramNodeParser $nodeParser)
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

        foreach ($this->nodeParser->parse($path) as $node) {
            $classDigagram->addNode($node)->addRelationships(...$node->relationships());
        }

        return $classDigagram;
    }
}
