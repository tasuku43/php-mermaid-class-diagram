<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagram;

use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\ClassDiagram;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\ClassDiagramBuilder;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Mermaid\AbstractClass_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Mermaid\Class_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Mermaid\Interface_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\NodeParser;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Relationship\Composition;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Relationship\Inheritance;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Relationship\Realization;

class ClassDiagramBuilderTest extends TestCase
{
    public function tesBuild_forDir(): void
    {
        $expectedDiagram = new ClassDiagram();

        $someClassA        = new Class_('SomeClassA');
        $someClassB        = new Class_('SomeClassB');
        $someClassC        = new Class_('SomeClassC');
        $someAbstructClass = new AbstractClass_('SomeAbstractClass');
        $someInterface     = new Interface_('SomeInterface');

        $someClassA->extends($someAbstructClass);
        $someClassA->composition($someClassB);
        $someClassA->composition($someClassC);
        $someAbstructClass->implements($someInterface);

        $expectedDiagram
            ->addNode($someAbstructClass)
            ->addNode($someClassA)
            ->addNode($someClassB)
            ->addNode($someClassC)
            ->addNode($someInterface);

        $expectedDiagram->addRelationships(new Realization($someAbstructClass, $someInterface))
            ->addRelationships(new Inheritance($someClassA, $someAbstructClass))
            ->addRelationships(new Composition($someClassA, $someClassB))
            ->addRelationships(new Composition($someClassA, $someClassC));

        $builder = new ClassDiagramBuilder(new NodeParser(
            (new ParserFactory)->create(ParserFactory::PREFER_PHP7),
            new NodeFinder()
        ));

        self::assertEquals($expectedDiagram, $builder->build(__DIR__ . '/data/'));
    }

    public function testBuild_forFilePath(): void
    {
        $expectedDiagram = new ClassDiagram();

        $someClass                = new Class_('SomeClassA');
        $defaultCompositionClass1 = new Class_('SomeClassB');
        $defaultCompositionClass2 = new Class_('SomeClassC');
        $defaultExtendsClass      = new Class_('SomeAbstractClass');
        $someClass->extends($defaultExtendsClass);
        $someClass->composition($defaultCompositionClass1);
        $someClass->composition($defaultCompositionClass2);

        $expectedDiagram
            ->addNode($someClass)
            ->addRelationships(new Inheritance($someClass, $defaultExtendsClass))
            ->addRelationships(new Composition($someClass, $defaultCompositionClass1))
            ->addRelationships(new Composition($someClass, $defaultCompositionClass2));

        $builder = new ClassDiagramBuilder(new NodeParser(
            (new ParserFactory)->create(ParserFactory::PREFER_PHP7),
            new NodeFinder()
        ));

        self::assertEquals($expectedDiagram, $builder->build(__DIR__ . '/data/SomeClassA.php'));
    }
}
