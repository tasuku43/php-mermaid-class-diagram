<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship;

use PHPUnit\Framework\TestCase;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Node;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Dependency;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Inheritance;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Realization;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Relationships;

class RelationshipsTest extends TestCase
{
    public function testSort(): void
    {
        $a = $this->mockNode('A');
        $b = $this->mockNode('B');
        $c = $this->mockNode('C');

        $relationships = Relationships::empty();

        // Intentionally add in non-sorted order by (from, to)
        $relationships->add(new Inheritance($c, $b));    // key: "C B"
        $relationships->add(new Dependency($a, $c));     // key: "A C"
        $relationships->add(new Realization($b, $a));    // key: "B A"

        $relationships->sort();
        $sorted = $relationships->getAll();

        $this->assertCount(3, $sorted);
        $this->assertInstanceOf(Dependency::class, $sorted[0]);   // A C
        $this->assertSame('A', $sorted[0]->from->nodeName());
        $this->assertSame('C', $sorted[0]->to->nodeName());

        $this->assertInstanceOf(Realization::class, $sorted[1]); // B A
        $this->assertSame('B', $sorted[1]->from->nodeName());
        $this->assertSame('A', $sorted[1]->to->nodeName());

        $this->assertInstanceOf(Inheritance::class, $sorted[2]); // C B
        $this->assertSame('C', $sorted[2]->from->nodeName());
        $this->assertSame('B', $sorted[2]->to->nodeName());
    }

    private function mockNode(string $name): Node
    {
        return new class($name) extends Node {
            public function render(): string
            {
                return "class {$this->name} {}";
            }
        };
    }
}

