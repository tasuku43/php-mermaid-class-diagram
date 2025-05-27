<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector;

use PHPUnit\Framework\TestCase;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Param;
use PhpParser\NodeFinder;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Class_ as DiagramClass;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector\CompositionConnector;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Nodes;

class CompositionConnectorTest extends TestCase
{
    /**
     * Test the connect method
     */
    public function testConnect(): void
    {
        // Set up diagram nodes
        $containerNode = new DiagramClass('Container');
        $containedNode = new DiagramClass('Contained');
        
        // Set up nodes collection
        $nodes = new Nodes();
        $nodes->add($containedNode);
        $nodes->add($containerNode);
        
        // Create connector
        $connector = new CompositionConnector('Container', ['Contained']);
        
        // Connect the nodes
        $connector->connect($nodes);
        
        // Verify the connection
        $relationships = $containerNode->relationships();
        $this->assertCount(1, $relationships);
        $this->assertStringContainsString('Container *-- Contained: composition', $relationships[0]->render());
    }
}
