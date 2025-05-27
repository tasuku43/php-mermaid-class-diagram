<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagramRenderer\Node;

use PHPUnit\Framework\TestCase;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Enum_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Interface_;

class Enum_Test extends TestCase
{
    /**
     * Test the enum rendering
     */
    public function testRender(): void
    {
        $enumName = 'TestEnum';
        $enum = new Enum_($enumName);
        
        $renderedEnum = $enum->render();
        
        $this->assertStringContainsString('class TestEnum {', $renderedEnum);
        // Check for the enum stereotype
        $this->assertStringContainsString('<<enum>>', $renderedEnum);
        $this->assertStringContainsString('}', $renderedEnum);
    }
    
    /**
     * Test that enum name is correctly set
     */
    public function testNodeName(): void
    {
        $enumName = 'TestEnum';
        $enum = new Enum_($enumName);
        
        $this->assertEquals($enumName, $enum->nodeName());
    }
    
    /**
     * Test that enum relationships work correctly
     */
    public function testRelationships(): void
    {
        $enum = new Enum_('TestEnum');
        $interface = new Interface_('TestInterface');
        
        $enum->implements($interface);
        
        $relationships = $enum->relationships();
        $this->assertCount(1, $relationships);
    }
}
