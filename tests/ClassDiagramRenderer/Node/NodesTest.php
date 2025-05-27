<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagramRenderer\Node;

use PHPUnit\Framework\TestCase;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Class_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Nodes;

class NodesTest extends TestCase
{
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
        
        $allNodes = $nodes->getAllNodes();
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
        $this->assertCount(0, $nodes->getAllNodes());
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
        
        $allNodes = $nodes->getAllNodes();
        $this->assertCount(1, $allNodes);
        $this->assertSame($classA2, $allNodes['ClassA']); // Should be the second instance
    }
}
