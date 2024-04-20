<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node;

use Closure;
use Exception;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\Parser;
use Symfony\Component\Finder\Finder;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector\CompositionConnector;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector\Connector;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector\InheritanceConnector;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector\RealizationConnector;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Exception\CannnotParseToClassLikeException;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Node as ClassDiagramNode;

class NodeParser
{
    /**
     * @var callable[]
     */
    private array $connectorParsers;

    public function __construct(
        private Parser     $parser,
        private NodeFinder $nodeFinder,
    )
    {
        $this->connectorParsers = [
            fn(Stmt\Interface_|Stmt\Class_ $classLike, ClassDiagramNode $classDiagramNode) => InheritanceConnector::parse($classLike, $classDiagramNode),
            fn(Stmt\Interface_|Stmt\Class_ $classLike, ClassDiagramNode $classDiagramNode) => RealizationConnector::parse($classLike, $classDiagramNode),
            fn(Stmt\Interface_|Stmt\Class_ $classLike, ClassDiagramNode $classDiagramNode) => CompositionConnector::parse($nodeFinder, $classLike, $classDiagramNode),
        ];
    }

    /**
     * @param string $path
     *
     * @return ClassDiagramNode[]
     *
     * @throws Exception
     */
    public function parse(string $path): array
    {
        $finder = str_ends_with($path, '.php')
            ? (new Finder())->in(pathinfo($path, PATHINFO_DIRNAME))->name(pathinfo($path, PATHINFO_BASENAME))->files()
            : (new Finder())->in($path)->name('*.php')->files();

        [$nodes, $connectors] = $this->extractClassInformation($finder);

        foreach ($connectors as $connector) {
            $connector->connect($nodes);
        }

        return $nodes;
    }

    /**
     * @return array{ClassDiagramNode[], Connector[]}
     * @throws Exception
     */
    private function extractClassInformation(Finder $finder): array
    {
        $nodes = $connectors = [];

        foreach ($finder as $file) {
            try {
                $classLike = $this->parseClassLike($file->getContents());
            } catch (CannnotParseToClassLikeException) {
                continue;
            }

            $classDiagramNode = $this->createClassDiagramNodeFromClassLike($classLike);

            $nodes[$classDiagramNode->nodeName()] = $classDiagramNode;

            foreach ($this->connectorParsers as $connectorParser) {
                $connectors[] = $connectorParser($classLike, $classDiagramNode);
            }
        }

        return [$nodes, $connectors];
    }

    private function parseClassLike(string $code): Stmt\Class_|Stmt\Interface_
    {
        $nodeTraverser = new NodeTraverser;
        $nodeTraverser->addVisitor(new NameResolver);
        $nodeTraverser->addVisitor(new ParentConnectingVisitor());

        $ast = $this->parser->parse($code);
        $ast = $nodeTraverser->traverse($ast);

        $classLike = $this->nodeFinder->findFirst($ast, function (Node $node) {
            return $node instanceof Stmt\Class_
                || $node instanceof Stmt\Interface_;
        });
        if (!($classLike instanceof Stmt\Class_ || $classLike instanceof Stmt\Interface_)) {
            throw new CannnotParseToClassLikeException();
        }

        return $classLike;
    }

    /**
     * @throws Exception
     */
    private function createClassDiagramNodeFromClassLike(ClassLike $classLike): ClassDiagramNode
    {
        return match (true) {
            $classLike instanceof Stmt\Class_ => $classLike->isAbstract()
                ? new AbstractClass_((string)$classLike->name->name)
                : new Class_((string)$classLike->name->name),
            $classLike instanceof Stmt\Interface_ => new Interface_((string)$classLike->name->name),
            default => throw new Exception('Unexpected match value')
        };
    }
}
