<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship;

use PHPUnit\Framework\TestCase;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Class_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Dependency;

class DependencyTest extends TestCase
{
    /**
     * Test that the dependency relationship renders correctly
     */
    public function testRender(): void
    {
        $dependentClass = new Class_('DependentClass');
        $dependedClass = new Class_('DependedClass');
        
        $dependency = new Dependency($dependentClass, $dependedClass);
        
        $rendered = $dependency->render();
        $this->assertStringContainsString('DependentClass ..> DependedClass', $rendered);
    }
}
