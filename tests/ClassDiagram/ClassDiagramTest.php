<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagram;

use PHPUnit\Framework\TestCase;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\ClassDiagram;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\AbstractClass_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Class_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Interface_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Composition;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Dependency;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Inheritance;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Relationship\Realization;

class ClassDiagramTest extends TestCase
{
    public function testRender(): void
    {
        $expectedClassDiagram = <<<EOM
            classDiagram
                class SomeAbstructClass {
                    <<abstract>>
                }
                class SomeClass01 {
                }
                class SomeClass02 {
                }
                class SomeClass03 {
                }
                class SomeInterface {
                    <<interface>>
                }

                SomeInterface <|.. SomeAbstructClass: realization
                SomeAbstructClass <|-- SomeClass01: inheritance
                SomeClass01 *-- SomeClass02: composition
                SomeClass03 <.. SomeClass01: dependency

            EOM;

        $diagram = new ClassDiagram();
        $diagram->addNode($someClass01 = new Class_('SomeClass01'));
        $diagram->addNode($someClass02 = new Class_('SomeClass02'));
        $diagram->addNode($someClass03 = new Class_('SomeClass03'));
        $diagram->addNode($someAbstructClass = new AbstractClass_('SomeAbstructClass'));
        $diagram->addNode($someInterface = new Interface_('SomeInterface'));

        $diagram->addRelationships(new Realization($someAbstructClass, $someInterface));
        $diagram->addRelationships(new Inheritance($someClass01, $someAbstructClass));
        $diagram->addRelationships(new Composition($someClass01, $someClass02));
        $diagram->addRelationships(new Dependency($someClass01, $someClass03));

        self::assertSame($expectedClassDiagram, $diagram->render());
    }
}
