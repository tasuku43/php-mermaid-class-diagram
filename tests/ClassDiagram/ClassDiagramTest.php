<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagram;

use PHPUnit\Framework\TestCase;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\ClassDiagram;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Mermaid\AbstractClass_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Mermaid\Class_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Mermaid\Interface_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Relationship\Composition;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Relationship\Inheritance;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Relationship\Realization;

class ClassDiagramTest extends TestCase
{
    public function testRender(): void
    {
        $expectedClassDiagram = <<<EOM
            classDiagram
                class SomeClass01 {
                }
                class SomeClass02 {
                }
                class SomeAbstructClass {
                    <<abstruct>>
                }
                class SomeInterface {
                    <<interface>>
                }

                SomeAbstructClass ..|> SomeInterface: realization
                SomeClass01 --|> SomeAbstructClass: inheritance
                SomeClass01 *-- SomeClass02: composition

            EOM;

        $diagram = new ClassDiagram();
        $diagram->addNode($someClass01 = new Class_('SomeClass01'));
        $diagram->addNode($someClass02 = new Class_('SomeClass02'));
        $diagram->addNode($someAbstructClass = new AbstractClass_('SomeAbstructClass'));
        $diagram->addNode($someInterface = new Interface_('SomeInterface'));

        $diagram->addRelationships(new Realization($someAbstructClass, $someInterface));
        $diagram->addRelationships(new Inheritance($someClass01, $someAbstructClass));
        $diagram->addRelationships(new Composition($someClass01, $someClass02));

        self::assertSame($expectedClassDiagram, $diagram->render());
    }
}
