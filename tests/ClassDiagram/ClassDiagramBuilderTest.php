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
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\DiagramNodeParser;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Mermaid\MermaidDiagramNodeMaker;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Relationship\Composition;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Relationship\Inheritance;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Relationship\Realization;

class ClassDiagramBuilderTest extends TestCase
{
    public function testBuild_forDir(): void
    {
        $expectedDiagram = new ClassDiagram();

        $someClassA        = new Class_('SomeClassA');
        $someClassB        = new Class_('SomeClassB');
        $someClassC        = new Class_('SomeClassC');
        $someClassD        = new Class_('SomeClassD');
        $someAbstructClass = new AbstractClass_('SomeAbstractClass');
        $someInterface     = new Interface_('SomeInterface');

        $someClassA->extends($someAbstructClass);
        $someClassA->composition($someClassB);
        $someClassA->composition($someClassC);
        $someAbstructClass->implements($someInterface);

        $expectedDiagram
            ->addNode($someClassC)
            ->addNode($someClassB)
            ->addNode($someAbstructClass)
            ->addNode($someClassA)
            ->addNode($someClassD)
            ->addNode($someInterface);

        $expectedDiagram->addRelationships(new Realization($someAbstructClass, $someInterface))
            ->addRelationships(new Inheritance($someClassA, $someAbstructClass))
            ->addRelationships(new Composition($someClassA, $someClassB))
            ->addRelationships(new Composition($someClassA, $someClassC));

        $acualDiagram = (new ClassDiagramBuilder(new DiagramNodeParser(
            (new ParserFactory)->create(ParserFactory::PREFER_PHP7),
            new NodeFinder(),
            new MermaidDiagramNodeMaker()
        )))->build(__DIR__ . '/data/');

        $this->assertEqualsDiagrams($expectedDiagram, $acualDiagram);
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

        $acualDiagram = (new ClassDiagramBuilder(new DiagramNodeParser(
            (new ParserFactory)->create(ParserFactory::PREFER_PHP7),
            new NodeFinder(),
            new MermaidDiagramNodeMaker()
        )))->build(__DIR__ . '/data/SomeClassA.php');

        $this->assertEqualsDiagrams($expectedDiagram, $acualDiagram);
    }

    public function assertEqualsDiagrams(ClassDiagram $expectedDiagram, ClassDiagram $acualDiagram): void
    {
        self::assertSame(count($expectedDiagram->getNodes()), count($acualDiagram->getNodes()));
        self::assertSame(count($expectedDiagram->getRelationships()), count($acualDiagram->getRelationships()));

        foreach ($expectedDiagram->getNodes() as $node) {
            self::assertContainsEquals($node, $acualDiagram->getNodes());
        }

        foreach ($expectedDiagram->getRelationships() as $node) {
            self::assertContainsEquals($node, $acualDiagram->getRelationships());
        }
    }
}
