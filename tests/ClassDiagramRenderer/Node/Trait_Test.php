<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagramRenderer\Node;

use PHPUnit\Framework\TestCase;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Class_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Trait_;

class Trait_Test extends TestCase
{
    public function testRender(): void
    {
        $trait = new Trait_('SampleTrait');
        $rendered = $trait->render();
        $this->assertStringContainsString('class SampleTrait {', $rendered);
        $this->assertStringContainsString('<<trait>>', $rendered);
    }

    public function testRelationships(): void
    {
        $trait = new Trait_('MyTrait');

        // Trait defines a composition and a dependency
        $trait->composition(new Class_('Composed'));
        $trait->depend(new Class_('Depended'));

        $relationships = $trait->relationships();

        // Expect exactly 2 relationships: composition and dependency
        $this->assertCount(2, $relationships);

        $rendered = array_map(fn($r) => $r->render(), $relationships);
        $this->assertTrue($this->contains($rendered, 'MyTrait *-- Composed: composition'));
        $this->assertTrue($this->contains($rendered, 'MyTrait ..> Depended: dependency'));
    }

    private function contains(array $lines, string $needle): bool
    {
        foreach ($lines as $line) {
            if (str_contains($line, $needle)) {
                return true;
            }
        }
        return false;
    }
}
