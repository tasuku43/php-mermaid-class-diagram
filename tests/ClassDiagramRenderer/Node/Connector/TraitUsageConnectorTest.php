<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector;

use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PHPUnit\Framework\TestCase;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PhpParser\NodeFinder;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Class_ as DiagramClass;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Nodes;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Trait_ as DiagramTrait;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector\TraitUsageConnector;

class TraitUsageConnectorTest extends TestCase
{
    public function testConnectRegistersTraitUsageOnly(): void
    {
        $using = new DiagramClass('Using');
        $trait = new DiagramTrait('T');

        // Trait declares composition(A) and dependency(B)
        $trait->composition(new DiagramClass('A'));
        $trait->depend(new DiagramClass('B'));

        $nodes = new Nodes();
        $nodes->add($using);
        $nodes->add($trait);

        $connector = new TraitUsageConnector('Using', ['T']);
        $connector->connect($nodes);

        // Verify via node relationships (connector application)
        $rendered = array_map(fn($r) => $r->render(), $using->relationships());
        $this->assertTrue($this->contains($rendered, 'Using *-- A: composition'));
        $this->assertTrue($this->contains($rendered, 'Using ..> B: dependency'));
    }

    /**
     * Test the parse method finds trait usages from AST
     */
    public function testParse(): void
    {
        $code = <<<'PHP'
        <?php
        trait T { private \A $a; }
        class Using { use T; }
        PHP;

        $parser = (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 1));
        $nodeFinder = new NodeFinder();
        $ast = $parser->parse($code);
        $classLike = $nodeFinder->findFirstInstanceOf($ast, \PhpParser\Node\Stmt\Class_::class);

        $using = new DiagramClass('Using');
        $connector = TraitUsageConnector::parse($nodeFinder, $classLike, $using);
        // Compare parsed connector state with expected
        $expected = new TraitUsageConnector('Using', ['T']);
        $this->assertEquals($expected, $connector);
    }

    public function testParseOnEnum(): void
    {
        $code = <<<'PHP'
        <?php
        trait T {}
        enum E { use T; }
        PHP;

        $parser = (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 1));
        $nodeFinder = new NodeFinder();
        $ast = $parser->parse($code);
        $enumLike = $nodeFinder->findFirstInstanceOf($ast, Enum_::class);

        $e = new DiagramClass('E');
        $connector = TraitUsageConnector::parse($nodeFinder, $enumLike, $e);
        $expected = new TraitUsageConnector('E', ['T']);
        $this->assertEquals($expected, $connector);
    }

    public function testParseOnInterface(): void
    {
        $code = <<<'PHP'
        <?php
        interface I {}
        PHP;

        $parser = (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 1));
        $nodeFinder = new NodeFinder();
        $ast = $parser->parse($code);
        $iface = $nodeFinder->findFirstInstanceOf($ast, Interface_::class);

        $i = new DiagramClass('I');
        $connector = TraitUsageConnector::parse($nodeFinder, $iface, $i);
        $expected = new TraitUsageConnector('I', []);
        $this->assertEquals($expected, $connector);
    }

    public function testParseOnTrait(): void
    {
        $code = <<<'PHP'
        <?php
        trait T {}
        trait T2 { use T; }
        PHP;

        $parser = (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 1));
        $nodeFinder = new NodeFinder();
        $ast = $parser->parse($code);
        $traits = $nodeFinder->findInstanceOf($ast, Trait_::class);
        $traitLike = null;
        foreach ($traits as $tr) {
            if ((string)$tr->name === 'T2') { $traitLike = $tr; break; }
        }

        $t = new DiagramTrait('T');
        $t->composition(new DiagramClass('A'));
        $t2 = new DiagramTrait('T2');
        $using = new DiagramClass('Using');

        // TraitUsage parsed on trait T2
        $connector = TraitUsageConnector::parse($nodeFinder, $traitLike, $t2);
        $expected = new TraitUsageConnector('T2', ['T']);
        $this->assertEquals($expected, $connector);
    }

    private function contains(array $lines, string $needle): bool
    {
        foreach ($lines as $line) {
            if (str_contains($line, $needle)) {
                return true;
            }
        }
        return false;
    }
}
