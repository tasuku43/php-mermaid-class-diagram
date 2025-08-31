<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector;

use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PHPUnit\Framework\TestCase;
use PhpParser\Node\Stmt\Class_;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Class_ as DiagramClass;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector\RealizationConnector;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Interface_ as DiagramInterface;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Nodes;

class RealizationConnectorTest extends TestCase
{
    /**
     * Test the connect method
     */
    public function testConnect(): void
    {
        $class = new DiagramClass('TestClass');
        $interface = new DiagramInterface('TestInterface');

        $nodes = new Nodes();
        $nodes->add($class);
        $nodes->add($interface);

        $connector = new RealizationConnector('TestClass', ['TestInterface']);
        $connector->connect($nodes);

        $node = $nodes->findByName('TestClass');

        $expected = new DiagramClass('TestClass');
        $expected->implements($interface);

        $this->assertEquals($node, $expected);
    }

    /**
     * Test the parse method collects realizations from AST
     */
    public function testParse(): void
    {
        $code = <<<'PHP'
        <?php
        interface I {}
        class C implements I {}
        PHP;

        $parser = (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 1));
        $ast = $parser->parse($code);

        // Find class node
        $classLike = null;
        foreach ($ast as $n) {
            if ($n instanceof Class_) { $classLike = $n; break; }
        }

        $classNode = new DiagramClass('C');
        $connector = RealizationConnector::parse($classLike, $classNode);
        // Compare parsed connector state with expected
        $expected = new RealizationConnector('C', ['I']);
        $this->assertEquals($expected, $connector);
    }

    public function testParseOnEnum(): void
    {
        $code = <<<'PHP'
        <?php
        interface I {}
        enum E implements I {}
        PHP;

        $parser = (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 1));
        $ast = $parser->parse($code);

        // Find enum node
        $enumLike = null;
        foreach ($ast as $n) {
            if ($n instanceof Enum_) { $enumLike = $n; break; }
        }

        $enumNode = new DiagramClass('E');
        $connector = RealizationConnector::parse($enumLike, $enumNode);
        $expected = new RealizationConnector('E', ['I']);
        $this->assertEquals($expected, $connector);
    }

    public function testParseOnTrait(): void
    {
        $code = <<<'PHP'
        <?php
        trait T {}
        PHP;

        $parser = (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 1));
        $ast = $parser->parse($code);

        // Find trait node
        $traitLike = null;
        foreach ($ast as $n) {
            if ($n instanceof Trait_) { $traitLike = $n; break; }
        }

        $tNode = new DiagramClass('T');
        $connector = RealizationConnector::parse($traitLike, $tNode);
        $expected = new RealizationConnector('T', []);
        $this->assertEquals($expected, $connector);
    }

    public function testParseOnInterface(): void
    {
        $code = <<<'PHP'
        <?php
        interface I {}
        PHP;

        $parser = (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 1));
        $ast = $parser->parse($code);

        $iface = null;
        foreach ($ast as $n) {
            if ($n instanceof Interface_) { $iface = $n; break; }
        }

        $iNode = new DiagramClass('I');
        $connector = RealizationConnector::parse($iface, $iNode);
        $expected = new RealizationConnector('I', []);
        $this->assertEquals($expected, $connector);
    }
}
