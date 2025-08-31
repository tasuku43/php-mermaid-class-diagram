<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagramRenderer\Node;

use PHPUnit\Framework\TestCase;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Class_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Node;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Nodes;

class NodesTest extends TestCase
{
    public function testSort(): void
    {
        $nodes = Nodes::empty();

        $c = $this->mockNode('C');
        $a = $this->mockNode('A');
        $b = $this->mockNode('B');

        // Add out of order
        $nodes->add($c)->add($a)->add($b);

        // Sort by name
        $nodes->sort();
        $sorted = array_values($nodes->getAll());

        $this->assertSame('A', $sorted[0]->nodeName());
        $this->assertSame('B', $sorted[1]->nodeName());
        $this->assertSame('C', $sorted[2]->nodeName());
    }

    private function mockNode(string $name): Node
    {
        return new class($name) extends Node {
            public function render(): string
            {
                return "class {$this->name} {}";
            }
        };
    }

    /**
     * Test the add and getAllNodes methods
     */
    public function testAddAndGetAllNodes(): void
    {
        $nodes = new Nodes();
        $classA = new Class_('ClassA');
        $classB = new Class_('ClassB');
        
        $nodes->add($classA);
        $nodes->add($classB);
        
        $allNodes = $nodes->getAll();
        $this->assertCount(2, $allNodes);
        $this->assertArrayHasKey('ClassA', $allNodes);
        $this->assertArrayHasKey('ClassB', $allNodes);
        $this->assertSame($classA, $allNodes['ClassA']);
        $this->assertSame($classB, $allNodes['ClassB']);
    }
    
    /**
     * Test the empty static method
     */
    public function testEmpty(): void
    {
        $nodes = Nodes::empty();
        
        $this->assertInstanceOf(Nodes::class, $nodes);
        $this->assertCount(0, $nodes->getAll());
    }
    
    /**
     * Test the findByName method
     */
    public function testFindByName(): void
    {
        $nodes = new Nodes();
        $classA = new Class_('ClassA');
        $classB = new Class_('ClassB');
        
        $nodes->add($classA);
        $nodes->add($classB);
        
        $foundClass = $nodes->findByName('ClassA');
        $this->assertSame($classA, $foundClass);
        
        $notFoundClass = $nodes->findByName('NonExistentClass');
        $this->assertNull($notFoundClass);
    }
    
    /**
     * Test adding a node with the same name twice
     */
    public function testAddSameNameTwice(): void
    {
        $nodes = new Nodes();
        $classA1 = new Class_('ClassA');
        $classA2 = new Class_('ClassA');
        
        $nodes->add($classA1);
        $nodes->add($classA2); // Should replace the first one
        
        $allNodes = $nodes->getAll();
        $this->assertCount(1, $allNodes);
        $this->assertSame($classA2, $allNodes['ClassA']); // Should be the second instance
    }
}
