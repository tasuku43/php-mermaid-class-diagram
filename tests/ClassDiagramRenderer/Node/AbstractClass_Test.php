<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagramRenderer\Node;

use PHPUnit\Framework\TestCase;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\AbstractClass_;

class AbstractClass_Test extends TestCase
{
    /**
     * Test the abstract class rendering
     */
    public function testRender(): void
    {
        $className = 'TestAbstractClass';
        $abstractClass = new AbstractClass_($className);
        
        $renderedClass = $abstractClass->render();
        
        $this->assertStringContainsString('class TestAbstractClass {', $renderedClass);
        // Check for the abstract stereotype
        $this->assertStringContainsString('<<abstract>>', $renderedClass);
        $this->assertStringContainsString('}', $renderedClass);
    }
    
    /**
     * Test that abstract class name is correctly set
     */
    public function testNodeName(): void
    {
        $className = 'TestAbstractClass';
        $abstractClass = new AbstractClass_($className);
        
        $this->assertEquals($className, $abstractClass->nodeName());
    }
    
    /**
     * Test that abstract class relationships work correctly
     */
    public function testRelationships(): void
    {
        $abstractClass = new AbstractClass_('TestAbstractClass');
        $anotherClass = new AbstractClass_('AnotherClass');
        
        $abstractClass->extends($anotherClass);
        
        $relationships = $abstractClass->relationships();
        $this->assertCount(1, $relationships);
    }
}
