<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector;

use PHPUnit\Framework\TestCase;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeFinder;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Class_ as DiagramClass;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector\InheritanceConnector;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Nodes;

class InheritanceConnectorTest extends TestCase
{
    /**
     * Test the connect method
     */
    public function testConnect(): void
    {
        // Set up diagram nodes
        $childNode = new DiagramClass('Child');
        $parentNode = new DiagramClass('Parent');
        
        // Set up nodes collection
        $nodes = new Nodes();
        $nodes->add($parentNode);
        $nodes->add($childNode);
        
        // Create connector
        $connector = new InheritanceConnector('Child', ['Parent']);
        
        // Connect the nodes
        $connector->connect($nodes);
        
        // Verify the connection
        $relationships = $childNode->relationships();
        $this->assertCount(1, $relationships);
        $this->assertStringContainsString('Parent <|-- Child: inheritance', $relationships[0]->render());
    }
}
