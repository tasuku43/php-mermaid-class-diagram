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
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector\DependencyConnector;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Nodes;

class DependencyConnectorTest extends TestCase
{
    /**
     * Test the connect method
     */
    public function testConnect(): void
    {
        // Set up diagram nodes
        $dependentNode = new DiagramClass('Dependent');
        $dependencyNode = new DiagramClass('Dependency');
        
        // Set up nodes collection
        $nodes = new Nodes();
        $nodes->add($dependencyNode);
        $nodes->add($dependentNode);
        
        // Create connector
        $connector = new DependencyConnector('Dependent', ['Dependency']);
        
        // Connect the nodes
        $connector->connect($nodes);
        
        // Verify the connection
        $relationships = $dependentNode->relationships();
        $this->assertCount(1, $relationships);
        $this->assertStringContainsString('Dependent ..> Dependency: dependency', $relationships[0]->render());
    }
}
