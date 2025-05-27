<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship;

use PHPUnit\Framework\TestCase;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Class_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Composition;

class CompositionTest extends TestCase
{
    /**
     * Test that the composition relationship renders correctly
     */
    public function testRender(): void
    {
        $containerClass = new Class_('ContainerClass');
        $containedClass = new Class_('ContainedClass');
        
        $composition = new Composition($containerClass, $containedClass);
        
        $rendered = $composition->render();
        $this->assertStringContainsString('ContainerClass *-- ContainedClass', $rendered);
    }
}
