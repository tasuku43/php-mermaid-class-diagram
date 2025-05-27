<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagramRenderer\Node;

use PHPUnit\Framework\TestCase;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Interface_;

class Interface_Test extends TestCase
{
    /**
     * Test the interface rendering
     */
    public function testRender(): void
    {
        $interfaceName = 'TestInterface';
        $interface = new Interface_($interfaceName);
        
        $renderedInterface = $interface->render();
        
        $this->assertStringContainsString('class TestInterface {', $renderedInterface);
        // Check for the interface stereotype
        $this->assertStringContainsString('<<interface>>', $renderedInterface);
        $this->assertStringContainsString('}', $renderedInterface);
    }
    
    /**
     * Test that interface name is correctly set
     */
    public function testNodeName(): void
    {
        $interfaceName = 'TestInterface';
        $interface = new Interface_($interfaceName);
        
        $this->assertEquals($interfaceName, $interface->nodeName());
    }
    
    /**
     * Test that interface relationships work correctly
     */
    public function testRelationships(): void
    {
        $interface = new Interface_('TestInterface');
        $anotherInterface = new Interface_('AnotherInterface');
        
        $interface->extends($anotherInterface);
        
        $relationships = $interface->relationships();
        $this->assertCount(1, $relationships);
    }
}
