<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector;

use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PHPUnit\Framework\TestCase;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Class_ as DiagramClass;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector\InheritanceConnector;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Nodes;

class InheritanceConnectorTest extends TestCase
{
    /**
     * Test the connect method
     */
    public function testConnect(): void
    {
        // Set up diagram nodes
        $childNode = new DiagramClass('Child');
        $parentNode = new DiagramClass('Parent');
        
        // Set up nodes collection
        $nodes = new Nodes();
        $nodes->add($parentNode);
        $nodes->add($childNode);
        
        // Create connector
        $connector = new InheritanceConnector('Child', ['Parent']);
        
        // Connect the nodes
        $connector->connect($nodes);
        
        // Verify the connection
        $relationships = $childNode->relationships();
        $this->assertCount(1, $relationships);
        $this->assertStringContainsString('Parent <|-- Child: inheritance', $relationships[0]->render());
    }

    /**
     * Test the parse method collects inheritance from AST
     */
    public function testParse(): void
    {
        $code = <<<'PHP'
        <?php
        class Child extends ParentX {}
        PHP;

        $parser = (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 1));
        $nodeFinder = new NodeFinder();
        $ast = $parser->parse($code);
        $classLike = $nodeFinder->findFirstInstanceOf($ast, Class_::class);

        $childNode = new DiagramClass('Child');
        $connector = InheritanceConnector::parse($classLike, $childNode);
        // Compare parsed connector state with expected
        $expected = new InheritanceConnector('Child', ['ParentX']);
        $this->assertEquals($expected, $connector);
    }

    public function testParseOnInterface(): void
    {
        $code = <<<'PHP'
        <?php
        interface C extends I {}
        PHP;

        $parser = (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 1));
        $nodeFinder = new NodeFinder();
        $ast = $parser->parse($code);
        $iface = $nodeFinder->findFirstInstanceOf($ast, Interface_::class);

        $cNode = new DiagramClass('C');
        $connector = InheritanceConnector::parse($iface, $cNode);
        $expected = new InheritanceConnector('C', ['I']);
        $this->assertEquals($expected, $connector);
    }

    public function testParseOnTrait(): void
    {
        $code = <<<'PHP'
        <?php
        trait T { }
        PHP;

        $parser = (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 1));
        $nodeFinder = new NodeFinder();
        $ast = $parser->parse($code);
        $traitLike = $nodeFinder->findFirstInstanceOf($ast, Trait_::class);

        $tNode = new DiagramClass('T');
        $connector = InheritanceConnector::parse($traitLike, $tNode);
        $expected = new InheritanceConnector('T', []);
        $this->assertEquals($expected, $connector);
    }

    public function testParseOnEnum(): void
    {
        $code = <<<'PHP'
        <?php
        enum E {}
        PHP;

        $parser = (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 1));
        $nodeFinder = new NodeFinder();
        $ast = $parser->parse($code);
        $enumLike = $nodeFinder->findFirstInstanceOf($ast, Enum_::class);

        $eNode = new DiagramClass('E');
        $connector = InheritanceConnector::parse($enumLike, $eNode);
        $expected = new InheritanceConnector('E', []);
        $this->assertEquals($expected, $connector);
    }
}
