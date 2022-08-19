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
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\NodeBuilder;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Relationship\Inheritance;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Relationship\Realization;

class ClassDiagramBuilderTest extends TestCase
{
    public function testBuild(): void
    {
        $expectedDiagram = new ClassDiagram();

        $someAbstructClass = new AbstractClass_('SomeAbstractClass');
        $someClass         = new Class_('SomeClass');
        $someInterface     = new Interface_('SomeInterface');

        $someClass->extends($someAbstructClass);
        $someAbstructClass->implements($someInterface);

        $expectedDiagram->addNode($someAbstructClass);
        $expectedDiagram->addNode($someClass);
        $expectedDiagram->addNode($someInterface);

        $expectedDiagram->addRelationships(new Realization($someAbstructClass, $someInterface));
        $expectedDiagram->addRelationships(new Inheritance($someClass, $someAbstructClass));


        $builder = new ClassDiagramBuilder(new NodeBuilder(
            (new ParserFactory)->create(ParserFactory::PREFER_PHP7),
            new NodeFinder()
        ));

        self::assertEquals($expectedDiagram, $builder->build(__DIR__ . '/data/'));
    }
}
