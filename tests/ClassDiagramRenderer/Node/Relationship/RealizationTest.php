<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship;

use PHPUnit\Framework\TestCase;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Class_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Interface_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Realization;

class RealizationTest extends TestCase
{
    /**
     * Test that the realization relationship renders correctly
     */
    public function testRender(): void
    {
        $implementingClass = new Class_('ImplementingClass');
        $interface = new Interface_('TestInterface');
        
        $realization = new Realization($implementingClass, $interface);
        
        $rendered = $realization->render();
        $this->assertStringContainsString('TestInterface <|.. ImplementingClass', $rendered);
    }
}
