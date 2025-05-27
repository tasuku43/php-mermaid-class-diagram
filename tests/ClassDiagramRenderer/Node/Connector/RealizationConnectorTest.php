<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector;

use PHPUnit\Framework\TestCase;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Class_ as DiagramClass;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector\RealizationConnector;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Interface_ as DiagramInterface;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Nodes;

class RealizationConnectorTest extends TestCase
{
    /**
     * Test the connect method
     */
    public function testConnect(): void
    {
        // Set up diagram nodes
        $classNode = new DiagramClass('TestClass');
        $interfaceNode = new DiagramInterface('TestInterface');
        
        // Set up nodes collection
        $nodes = new Nodes();
        $nodes->add($interfaceNode);
        $nodes->add($classNode);
        
        // Create connector
        $connector = new RealizationConnector('TestClass', ['TestInterface']);
        
        // Connect the nodes
        $connector->connect($nodes);
        
        // Verify the connection
        $relationships = $classNode->relationships();
        $this->assertCount(1, $relationships);
        $this->assertStringContainsString('TestInterface <|.. TestClass: realization', $relationships[0]->render());
    }
}
