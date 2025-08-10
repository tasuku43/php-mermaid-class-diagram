<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector;

use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PHPUnit\Framework\TestCase;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Class_ as DiagramClass;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector\CompositionConnector;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Nodes;

class CompositionConnectorTest extends TestCase
{
    /**
     * Test the connect method
     */
    public function testConnect(): void
    {
        // Set up diagram nodes
        $containerNode = new DiagramClass('Container');
        $containedNode = new DiagramClass('Contained');
        
        // Set up nodes collection
        $nodes = new Nodes();
        $nodes->add($containedNode);
        $nodes->add($containerNode);
        
        // Create connector
        $connector = new CompositionConnector('Container', ['Contained']);
        
        // Connect the nodes
        $connector->connect($nodes);
        
        // Verify the connection
        $relationships = $containerNode->relationships();
        $this->assertCount(1, $relationships);
        $this->assertStringContainsString('Container *-- Contained: composition', $relationships[0]->render());
    }

    /**
     * Test the parse method collects compositions from AST
     */
    public function testParse(): void
    {
        $code = <<<'PHP'
        <?php
        class Container {
            public function __construct(public \Contained $c) {}
            private \AnotherContained $p;
        }
        PHP;

        $parser = (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 1));
        $nodeFinder = new NodeFinder();
        $ast = $parser->parse($code);
        $classLike = $nodeFinder->findFirstInstanceOf($ast, Class_::class);

        $containerNode = new DiagramClass('Container');
        $connector = CompositionConnector::parse($nodeFinder, $classLike, $containerNode);
        // Compare parsed connector state with expected
        $expected = new CompositionConnector('Container', ['AnotherContained', 'Contained']);
        $this->assertEquals($expected, $connector);
    }

    public function testParseOnEnum(): void
    {
        $code = <<<'PHP'
        <?php
        enum E {
            private \Contained $c;
        }
        PHP;

        $parser = (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 1));
        $nodeFinder = new NodeFinder();
        $ast = $parser->parse($code);
        $enumLike = $nodeFinder->findFirstInstanceOf($ast, \PhpParser\Node\Stmt\Enum_::class);

        $enumNode = new DiagramClass('E');
        $connector = CompositionConnector::parse($nodeFinder, $enumLike, $enumNode);
        $expected = new CompositionConnector('E', ['Contained']);
        $this->assertEquals($expected, $connector);
    }

    public function testParseOnTrait(): void
    {
        $code = <<<'PHP'
        <?php
        trait T { private \Contained $p; }
        PHP;

        $parser = (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 1));
        $nodeFinder = new NodeFinder();
        $ast = $parser->parse($code);
        $traitLike = $nodeFinder->findFirstInstanceOf($ast, Trait_::class);

        $tNode = new DiagramClass('T');
        $connector = CompositionConnector::parse($nodeFinder, $traitLike, $tNode);
        $expected = new CompositionConnector('T', ['Contained']);
        $this->assertEquals($expected, $connector);
    }

    public function testParseOnInterface(): void
    {
        $code = <<<'PHP'
        <?php
        interface I { /* no properties allowed in interface */ }
        PHP;

        $parser = (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 1));
        $nodeFinder = new NodeFinder();
        $ast = $parser->parse($code);
        $iface = $nodeFinder->findFirstInstanceOf($ast, Interface_::class);

        $iNode = new DiagramClass('I');
        $connector = CompositionConnector::parse($nodeFinder, $iface, $iNode);
        $expected = new CompositionConnector('I', []);
        $this->assertEquals($expected, $connector);
    }
}
