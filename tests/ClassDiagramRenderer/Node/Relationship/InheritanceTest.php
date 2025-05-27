<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship;

use PHPUnit\Framework\TestCase;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Class_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Inheritance;

class InheritanceTest extends TestCase
{
    /**
     * Test that the inheritance relationship renders correctly
     */
    public function testRender(): void
    {
        $childClass = new Class_('ChildClass');
        $parentClass = new Class_('ParentClass');
        
        $inheritance = new Inheritance($childClass, $parentClass);
        
        $rendered = $inheritance->render();
        $this->assertStringContainsString('ParentClass <|-- ChildClass', $rendered);
    }
}
