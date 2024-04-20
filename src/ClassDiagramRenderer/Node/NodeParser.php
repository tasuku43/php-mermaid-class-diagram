<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node;

use Exception;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
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
    public function __construct(
        private Parser     $parser,
        private NodeFinder $nodeFinder,
    )
    {
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

            $connectors[] = $this->extractExtendedClassesOrInterfaces($classLike, $classDiagramNode);
            $connectors[] = $this->extractImplementedInterfaces($classLike, $classDiagramNode);
            $connectors[] = $this->extractClassOrInterfaceProperties($classLike, $classDiagramNode);
            $connectors[] = $this->extractPropertiesFromConstructor($classLike, $classDiagramNode);
        }

        return [$nodes, $connectors];
    }

    private function parseClassLike(string $code): Node\Stmt\Class_|Node\Stmt\Interface_
    {
        $nodeTraverser = new NodeTraverser;
        $nodeTraverser->addVisitor(new NameResolver);
        $nodeTraverser->addVisitor(new ParentConnectingVisitor());

        $ast = $this->parser->parse($code);
        $ast = $nodeTraverser->traverse($ast);

        $classLike = $this->nodeFinder->findFirst($ast, function (Node $node) {
            return $node instanceof Node\Stmt\Class_
                || $node instanceof Node\Stmt\Interface_;
        });
        if (!($classLike instanceof Node\Stmt\Class_ || $classLike instanceof Node\Stmt\Interface_)) {
            throw new CannnotParseToClassLikeException();
        }

        return $classLike;
    }

    private function extractExtendedClassesOrInterfaces(
        Node\Stmt\Interface_|Node\Stmt\Class_ $classLike,
        ClassDiagramNode                      $classDiagramNode,
    ): InheritanceConnector
    {
        $extendsNodeNames = [];

        if ($classLike->extends !== null) {
            $extendsNodeNames = is_array($classLike->extends)
                ? array_map(function (Node\Name $name) {
                    return (string)$name->getLast();
                }, $classLike->extends)
                : [(string)$classLike->extends->getLast()];
        }

        return new InheritanceConnector($classDiagramNode->nodeName(), $extendsNodeNames);
    }

    private function extractImplementedInterfaces(
        Node\Stmt\Interface_|Node\Stmt\Class_ $classLike,
        ClassDiagramNode                      $classDiagramNode,
    ): RealizationConnector
    {
        $implementsNodeNames = [];

        if (property_exists($classLike, 'implements') && $classLike->implements !== []) {
            $implementsNodeNames = array_map(function (Node\Name $name) {
                return (string)$name->getLast();
            }, $classLike->implements);
        }

        return new RealizationConnector($classDiagramNode->nodeName(), $implementsNodeNames);
    }

    private function extractClassOrInterfaceProperties(
        Node\Stmt\Interface_|Node\Stmt\Class_ $classLike,
        ClassDiagramNode                      $classDiagramNode,
    ): CompositionConnector
    {
        $propertieNodeNames = array_map(function (Property $property) {
            return $property->type->getLast();
        }, array_filter($classLike->getProperties(),
                fn(Property $property) => $property->type instanceof FullyQualified)
        );
        return new CompositionConnector($classDiagramNode->nodeName(), $propertieNodeNames);
    }

    /**
     * @throws Exception
     */
    private function createClassDiagramNodeFromClassLike(ClassLike $classLike): ClassDiagramNode
    {
        return match (true) {
            $classLike instanceof Node\Stmt\Class_ => $classLike->isAbstract()
                ? new AbstractClass_((string)$classLike->name->name)
                : new Class_((string)$classLike->name->name),
            $classLike instanceof Node\Stmt\Interface_ => new Interface_((string)$classLike->name->name),
            default => throw new Exception('Unexpected match value')
        };
    }

    private function extractPropertiesFromConstructor(
        Node\Stmt\Interface_|Node\Stmt\Class_ $classLike,
        ClassDiagramNode                      $classDiagramNode,
    ): CompositionConnector
    {
        $propertieNodeNames = [];

        $construct = $this->nodeFinder->findFirst($classLike, function (Node $node) {
            return $node instanceof ClassMethod && (string)$node->name === '__construct';
        });
        if ($construct !== null) {
            assert($construct instanceof ClassMethod);
            foreach (array_filter($construct->getParams(), fn(Node\Param $param) => $param->type instanceof Name) as $param) {
                assert($param instanceof Node\Param);

                // If `visibirity` is not specified, flags is 0
                if ($param->flags !== 0) {
                    $propertieNodeNames = array_merge(
                        $propertieNodeNames,
                        [$param->type->getLast()]
                    );
                }
            }
        }
        return new CompositionConnector($classDiagramNode->nodeName(), $propertieNodeNames);
    }
}
