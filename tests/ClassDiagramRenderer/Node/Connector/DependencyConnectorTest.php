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
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector\DependencyConnector;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Nodes;

class DependencyConnectorTest extends TestCase
{
    /**
     * Test the connect method
     */
    public function testConnect(): void
    {
        $dependent = new DiagramClass('Dependent');
        $dependency = new DiagramClass('Dependency');

        $nodes = new Nodes();
        $nodes->add($dependent);
        $nodes->add($dependency);

        $connector = new DependencyConnector('Dependent', ['Dependency']);
        $connector->connect($nodes);

        $node = $nodes->findByName('Dependent');

        $expected = new DiagramClass('Dependent');
        $expected->depend($dependency);

        $this->assertEquals($node, $expected);
    }

    /**
     * Test the parse method collects dependencies from AST
     */
    public function testParse(): void
    {
        $code = <<<'PHP'
        <?php
        class UsingDeps {
            public function m(\ParamType $p): \ReturnType {
                $x = new \NewType();
                return new \ReturnType();
            }
        }
        PHP;

        $parser = (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 1));
        $nodeFinder = new NodeFinder();
        $ast = $parser->parse($code);
        $classLike = $nodeFinder->findFirstInstanceOf($ast, Class_::class);

        $usingNode = new DiagramClass('UsingDeps');
        $connector = DependencyConnector::parse($nodeFinder, $classLike, $usingNode);
        // Compare parsed connector state with expected
        $expected = new DependencyConnector('UsingDeps', ['ParamType', 'ReturnType', 'NewType']);
        $this->assertEquals($expected, $connector);
    }

    public function testParseOnInterface(): void
    {
        $code = <<<'PHP'
        <?php
        interface I {
            public function m(\ParamType $p): \ReturnType;
        }
        PHP;

        $parser = (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 1));
        $nodeFinder = new NodeFinder();
        $ast = $parser->parse($code);
        $iface = $nodeFinder->findFirstInstanceOf($ast, Interface_::class);

        $node = new DiagramClass('I');
        $connector = DependencyConnector::parse($nodeFinder, $iface, $node);
        $expected = new DependencyConnector('I', ['ParamType', 'ReturnType']);
        $this->assertEquals($expected, $connector);
    }

    public function testParseOnEnum(): void
    {
        $code = <<<'PHP'
        <?php
        enum E {
            public function m(\X $x): \Y { return new \Y(); }
        }
        PHP;

        $parser = (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 1));
        $nodeFinder = new NodeFinder();
        $ast = $parser->parse($code);
        $enumLike = $nodeFinder->findFirstInstanceOf($ast, \PhpParser\Node\Stmt\Enum_::class);

        $node = new DiagramClass('E');
        $connector = DependencyConnector::parse($nodeFinder, $enumLike, $node);
        $expected = new DependencyConnector('E', ['X', 'Y']);
        $this->assertEquals($expected, $connector);
    }

    public function testParseOnTrait(): void
    {
        $code = <<<'PHP'
        <?php
        trait T {
            public function m(\P $p): \R { $a = new \N(); return new \R(); }
        }
        PHP;

        $parser = (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 1));
        $nodeFinder = new NodeFinder();
        $ast = $parser->parse($code);
        $traitLike = $nodeFinder->findFirstInstanceOf($ast, Trait_::class);

        $node = new DiagramClass('T');
        $connector = DependencyConnector::parse($nodeFinder, $traitLike, $node);
        $expected = new DependencyConnector('T', ['P', 'R', 'N']);
        $this->assertEquals($expected, $connector);
    }
}
