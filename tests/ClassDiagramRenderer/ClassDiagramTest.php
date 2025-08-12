<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagramRenderer;

use PHPUnit\Framework\TestCase;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\ClassDiagram;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Class_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Interface_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Inheritance;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Realization;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\RenderOptions\RenderOptions;

class ClassDiagramTest extends TestCase
{
    /**
     * Test that adding a node works correctly
     */
    public function testAddNode(): void
    {
        $diagram = new ClassDiagram();
        $class = new Class_('TestClass');
        
        $resultDiagram = $diagram->addNode($class);
        
        // Method should return $this for method chaining
        $this->assertSame($diagram, $resultDiagram);
        
        // Verify the node is included in the rendered output
        $rendered = $diagram->render(RenderOptions::default());
        $expectedOutput = <<<'EOT'
classDiagram
    class TestClass {
    }


EOT;
        $this->assertSame($expectedOutput, $rendered);
    }
    
    /**
     * Test that adding relationships works correctly
     */
    public function testAddRelationships(): void
    {
        $diagram = new ClassDiagram();
        $class1 = new Class_('Class1');
        $class2 = new Class_('Class2');
        
        $inheritance = new Inheritance($class1, $class2);
        
        $resultDiagram = $diagram->addRelationships($inheritance);
        
        // Method should return $this for method chaining
        $this->assertSame($diagram, $resultDiagram);
        
        // Add the nodes so they appear in the diagram
        $diagram->addNode($class1)->addNode($class2);
        
        // Verify the relationship is included in the rendered output
        $rendered = $diagram->render(RenderOptions::default());
        $expectedOutput = <<<'EOT'
classDiagram
    class Class1 {
    }
    class Class2 {
    }

    Class2 <|-- Class1: inheritance

EOT;
        $this->assertSame($expectedOutput, $rendered);
    }
    
    /**
     * Test the render method outputs proper Mermaid format
     */
    public function testRender(): void
    {
        $diagram = new ClassDiagram();
        $class = new Class_('TestClass');
        $interface = new Interface_('TestInterface');
        
        $relationship = new Realization($class, $interface);
        
        $diagram->addNode($class)
                ->addNode($interface)
                ->addRelationships($relationship);
        
        $rendered = $diagram->render(RenderOptions::default());
        $expectedOutput = <<<'EOT'
classDiagram
    class TestClass {
    }
    class TestInterface {
        <<interface>>
    }

    TestInterface <|.. TestClass: realization

EOT;
        $this->assertSame($expectedOutput, $rendered);
    }
}
