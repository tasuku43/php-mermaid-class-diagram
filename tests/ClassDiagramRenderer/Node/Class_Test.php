<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagramRenderer\Node;

use PHPUnit\Framework\TestCase;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Class_;

class Class_Test extends TestCase
{
    /**
     * Test the basic class rendering
     */
    public function testRender(): void
    {
        $className = 'TestClass';
        $class = new Class_($className);
        
        $renderedClass = $class->render();
        
        $this->assertStringContainsString('class TestClass {', $renderedClass);
        $this->assertStringContainsString('}', $renderedClass);
        $this->assertStringNotContainsString('<<', $renderedClass);
    }
    
    /**
     * Test that class name is correctly set
     */
    public function testNodeName(): void
    {
        $className = 'TestClass';
        $class = new Class_($className);
        
        $this->assertEquals($className, $class->nodeName());
    }
    
    /**
     * Test that class relationships work correctly
     */
    public function testRelationships(): void
    {
        $class = new Class_('TestClass');
        $anotherClass = new Class_('AnotherClass');
        
        $class->extends($anotherClass);
        
        $relationships = $class->relationships();
        $this->assertCount(1, $relationships);
    }
}
