<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagramRenderer\Node;

use PHPUnit\Framework\TestCase;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Node;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Nodes;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Composition;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Dependency;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Inheritance;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Realization;

class NodeTest extends TestCase
{
    /**
     * Test the nodeName method
     */
    public function testNodeName(): void
    {
        $nodeName = 'TestNode';
        $node = $this->createMockNode($nodeName);
        
        $this->assertEquals($nodeName, $node->nodeName());
    }
    
    /**
     * Test the extends method
     */
    public function testExtends(): void
    {
        $node = $this->createMockNode('TestNode');
        $extendsNode = $this->createMockNode('ExtendsNode');
        
        $node->extends($extendsNode);
        
        $relationships = $node->relationships();
        $this->assertCount(1, $relationships);
        $this->assertInstanceOf(Inheritance::class, $relationships[0]);
    }
    
    /**
     * Test the implements method
     */
    public function testImplements(): void
    {
        $node = $this->createMockNode('TestNode');
        $implementsNode = $this->createMockNode('ImplementsNode');
        
        $node->implements($implementsNode);
        
        $relationships = $node->relationships();
        $this->assertCount(1, $relationships);
        $this->assertInstanceOf(Realization::class, $relationships[0]);
    }
    
    /**
     * Test the composition method
     */
    public function testComposition(): void
    {
        $node = $this->createMockNode('TestNode');
        $compositionNode = $this->createMockNode('CompositionNode');
        
        $node->composition($compositionNode);
        
        $relationships = $node->relationships();
        $this->assertCount(1, $relationships);
        $this->assertInstanceOf(Composition::class, $relationships[0]);
    }
    
    /**
     * Test the depend method
     */
    public function testDepend(): void
    {
        $node = $this->createMockNode('TestNode');
        $dependNode = $this->createMockNode('DependNode');
        
        $node->depend($dependNode);
        
        $relationships = $node->relationships();
        $this->assertCount(1, $relationships);
        $this->assertInstanceOf(Dependency::class, $relationships[0]);
    }
    
    /**
     * Test the sortNodes static method
     */
    public function testSortNodes(): void
    {
        $nodeC = $this->createMockNode('C');
        $nodeA = $this->createMockNode('A');
        $nodeB = $this->createMockNode('B');
        
        $nodes = [$nodeC, $nodeA, $nodeB];
        
        Node::sortNodes($nodes);
        
        $this->assertEquals('A', $nodes[0]->nodeName());
        $this->assertEquals('B', $nodes[1]->nodeName());
        $this->assertEquals('C', $nodes[2]->nodeName());
    }
    
    /**
     * Test multiple relationships
     */
    public function testMultipleRelationships(): void
    {
        $node = $this->createMockNode('TestNode');
        $extendsNode = $this->createMockNode('ExtendsNode');
        $implementsNode = $this->createMockNode('ImplementsNode');
        $compositionNode = $this->createMockNode('CompositionNode');
        $dependNode = $this->createMockNode('DependNode');
        
        $node->extends($extendsNode);
        $node->implements($implementsNode);
        $node->composition($compositionNode);
        $node->depend($dependNode);
        
        $relationships = $node->relationships();
        $this->assertCount(4, $relationships);
    }

    /**
     * Create a test implementation of the abstract Node class
     */
    private function createMockNode(string $name): Node
    {
        return new class($name) extends Node {
            public function render(): string
            {
                return "class {$this->name} {}";
            }
        };
    }
}
