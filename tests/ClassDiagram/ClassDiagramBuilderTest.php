<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagram;

use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\ClassDiagram;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\ClassDiagramBuilder;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\AbstractClass_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Class_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Interface_;
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
        $someAbstructClass = new AbstractClass_('SomeAbstractClass');
        $someInterface     = new Interface_('SomeInterface');

        $someClassA->extends($someAbstructClass);
        $someClassA->composition($someClassB);
        $someAbstructClass->implements($someInterface);

        $expectedDiagram
            ->addNode($someAbstructClass)
            ->addNode($someClassA)
            ->addNode($someClassB)
            ->addNode($someInterface);

        $expectedDiagram->addRelationships(new Realization($someAbstructClass, $someInterface))
            ->addRelationships(new Inheritance($someClassA, $someAbstructClass))
            ->addRelationships(new Composition($someClassA, $someClassB));

        $builder = new ClassDiagramBuilder(new NodeParser(
            (new ParserFactory)->create(ParserFactory::PREFER_PHP7),
            new NodeFinder()
        ));

        self::assertEquals($expectedDiagram, $builder->build(__DIR__ . '/data/'));
    }

    public function testBuild_forFilePath(): void
    {
        $expectedDiagram = new ClassDiagram();

        $someClass               = new Class_('SomeClassA');
        $defaultCompositionClass = new Class_('SomeClassB');
        $defaultExtendsClass     = new Class_('SomeAbstractClass');
        $someClass->extends($defaultExtendsClass);
        $someClass->composition($defaultCompositionClass);

        $expectedDiagram
            ->addNode($someClass)
            ->addRelationships(new Inheritance($someClass, $defaultExtendsClass))
            ->addRelationships(new Composition($someClass, $defaultCompositionClass));

        $builder = new ClassDiagramBuilder(new NodeParser(
            (new ParserFactory)->create(ParserFactory::PREFER_PHP7),
            new NodeFinder()
        ));

        self::assertEquals($expectedDiagram, $builder->build(__DIR__ . '/data/SomeClassA.php'));
    }
}
