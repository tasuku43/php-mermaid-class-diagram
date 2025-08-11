<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagramRenderer\Node;

use PHPUnit\Framework\TestCase;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Class_ as DiagramClass;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Trait_ as DiagramTrait;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Composition;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Dependency;

class TraitAggregationTest extends TestCase
{
    public function testClassInheritsTraitDependenciesAndCompositions(): void
    {
        $trait = new DiagramTrait('T');
        $dep = new DiagramClass('Dep');
        $prop = new DiagramClass('Prop');
        $trait->depend($dep);
        $trait->composition($prop);

        $using = new DiagramClass('Using');
        $using->useTrait($trait);

        $relationships = $using->relationships();

        $this->assertTrue($this->hasRelationship($relationships, Composition::class, 'Using', 'Prop'));
        $this->assertTrue($this->hasRelationship($relationships, Dependency::class, 'Using', 'Dep'));
    }

    public function testNestedTraitDependenciesAreAggregated(): void
    {
        $t1 = new DiagramTrait('T1');
        $t1->depend(new DiagramClass('D1'));

        $t2 = new DiagramTrait('T2');
        $t2->useTrait($t1);

        $using = new DiagramClass('Using');
        $using->useTrait($t2);

        $relationships = $using->relationships();
        $this->assertTrue($this->hasRelationship($relationships, Dependency::class, 'Using', 'D1'));
    }

    private function hasRelationship(array $relationships, string $class, string $from, string $to): bool
    {
        foreach ($relationships as $rel) {
            if ($rel instanceof $class) {
                $render = $rel->render();
                if (str_contains($render, $from) && str_contains($render, $to)) {
                    return true;
                }
            }
        }
        return false;
    }
}

