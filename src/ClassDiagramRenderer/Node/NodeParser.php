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

        [$nodes, $extends, $implements, $properties] = $this->extractClassInformation($finder);

        return $this->configureNodeRelations($extends, $implements, $properties, $nodes);
    }

    /**
     * @return array{ClassDiagramNode[], array<string, string[]>, array<string, string[]>, array<string, string[]>}
     * @throws Exception
     */
    private function extractClassInformation(Finder $finder): array
    {
        $nodes = $extends = $implements = $properties = [];

        foreach ($finder as $file) {
            try {
                $classLike = $this->parseClassLike($file->getContents());
            } catch (CannnotParseToClassLikeException) {
                continue;
            }

            $classDiagramNode = $this->createClassDiagramNodeFromClassLike($classLike);

            $nodes[$classDiagramNode->nodeName()] = $classDiagramNode;

            $extends    = $this->extractExtendedClassesOrInterfaces($classLike, $classDiagramNode, $extends);
            $implements = $this->extractImplementedInterfaces($classLike, $classDiagramNode, $implements);
            $properties = $this->extractClassOrInterfaceProperties($classLike, $classDiagramNode, $properties);
            $properties = $this->extractPropertiesFromConstructor($classLike, $classDiagramNode, $properties);
        }
        return [$nodes, $extends, $implements, $properties];
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

    private function createDefaultExtendsNode(ClassDiagramNode $extended, string $extendsNodeName): ClassDiagramNode
    {
        return match (true) {
            $extended instanceof Interface_ => new Interface_($extendsNodeName),
            default => new Class_($extendsNodeName),
        };
    }

    /**
     * @return string[]
     */
    private function extractExtendedClassesOrInterfaces(
        Node\Stmt\Interface_|Node\Stmt\Class_ $classLike,
        ClassDiagramNode                      $classDiagramNode,
        array                                 $extends
    ): array
    {
        if ($classLike->extends !== null) {
            $extends[$classDiagramNode->nodeName()] = is_array($classLike->extends)
                ? array_map(function (Node\Name $name) {
                    return (string)$name->getLast();
                }, $classLike->extends)
                : [(string)$classLike->extends->getLast()];
        }
        return $extends;
    }

    /**
     * @return string[]
     */
    private function extractImplementedInterfaces(
        Node\Stmt\Interface_|Node\Stmt\Class_ $classLike,
        ClassDiagramNode                      $classDiagramNode,
        array                                 $implements
    ): array
    {
        if (property_exists($classLike, 'implements') && $classLike->implements !== []) {
            $implements[$classDiagramNode->nodeName()] = array_map(function (Node\Name $name) {
                return (string)$name->getLast();
            }, $classLike->implements);
        }
        return $implements;
    }

    /**
     * @return string[]
     */
    private function extractClassOrInterfaceProperties(
        Node\Stmt\Interface_|Node\Stmt\Class_ $classLike,
        ClassDiagramNode                      $classDiagramNode,
        array                                 $properties
    ): array
    {
        $properties[$classDiagramNode->nodeName()] = array_map(function (Property $property) {
            return $property->type->getLast();
        }, array_filter($classLike->getProperties(),
                fn(Property $property) => $property->type instanceof FullyQualified)
        );
        return $properties;
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

    /**
     * @return string[]
     */
    private function extractPropertiesFromConstructor(
        Node\Stmt\Interface_|Node\Stmt\Class_ $classLike,
        ClassDiagramNode                      $classDiagramNode,
        array                                 $properties
    ): array
    {
        $construct = $this->nodeFinder->findFirst($classLike, function (Node $node) {
            return $node instanceof ClassMethod && (string)$node->name === '__construct';
        });
        if ($construct !== null) {
            assert($construct instanceof ClassMethod);
            foreach (array_filter($construct->getParams(), fn(Node\Param $param) => $param->type instanceof Name) as $param) {
                assert($param instanceof Node\Param);

                // If `visibirity` is not specified, flags is 0
                if ($param->flags !== 0) {
                    $properties[$classDiagramNode->nodeName()] = array_merge(
                        $properties[$classDiagramNode->nodeName()],
                        [$param->type->getLast()]
                    );
                }
            }
        }
        return $properties;
    }

    /**
     * @return ClassDiagramNode[]
     */
    private function configureNodeRelations(array $extends, array $implements, array $properties, array $nodes): array
    {
        return array_values(array_map(function (ClassDiagramNode $node) use ($extends, $implements, $properties) {
            $nodeName = $node->nodeName();
            foreach ($extends[$nodeName] ?? [] as $extendsName) {
                $node->extends(
                    $nodes[$extendsName] ?? $this->createDefaultExtendsNode($node, $extendsName)
                );
            }
            foreach ($implements[$nodeName] ?? [] as $implementsName) {
                $node->implements($nodes[$implementsName] ?? new Interface_($implementsName));
            }
            foreach ($properties[$nodeName] ?? [] as $propertyName) {
                $node->composition($nodes[$propertyName] ?? new Class_($propertyName));
            }
            return $node;
        }, $nodes));
    }
}
