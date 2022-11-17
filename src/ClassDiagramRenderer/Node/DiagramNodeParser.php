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
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Mermaid\AbstractClass_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Mermaid\Class_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Mermaid\Interface_;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Mermaid\MermaidDiagramNode as ClassDiagramNode;

class DiagramNodeParser
{
    public function __construct(
        private Parser     $parser,
        private NodeFinder $nodeFinder,
    )
    {
    }

    /**
     * @param string $path
     * @return ClassDiagramNode[]
     *
     * @throws Exception
     */
    public function parse(string $path): array
    {
        $finder = str_ends_with($path, '.php')
            ? (new Finder())->in(pathinfo($path, PATHINFO_DIRNAME))->name(pathinfo($path, PATHINFO_BASENAME))->files()
            : (new Finder())->in($path)->name('*.php')->files();

        /** @var ClassDiagramNode $nodes */
        $nodes = [];
        /** @var array<string, array> $extends */
        $extends = [];
        /** @var  array<string, array> $implements */
        $implements = [];
        /** @var  array<string, array> $properties */
        $properties = [];

        foreach ($finder as $file) {
            try {
                $classLike = $this->parseClassLike($file->getContents());
            } catch (CannnotParseToClassLikeException) {
                continue;
            }

            $classDiagramNode = match (true) {
                $classLike instanceof Node\Stmt\Class_ => $classLike->isAbstract()
                    ? new AbstractClass_((string)$classLike->name->name)
                    : new Class_((string)$classLike->name->name),
                $classLike instanceof Node\Stmt\Interface_ => new Interface_((string)$classLike->name->name),
                default => throw new Exception('Unexpected match value')
            };

            $nodes[$classDiagramNode->nodeName()] = $classDiagramNode;

            if ($classLike->extends !== null) {
                $extends[$classDiagramNode->nodeName()] = is_array($classLike->extends)
                    ? array_map(function (Node\Name $name) {
                        return (string) $name->getLast();
                    }, $classLike->extends)
                    : [(string) $classLike->extends->getLast()];
            }

            if (property_exists($classLike, 'implements') && $classLike->implements !== []) {
                $implements[$classDiagramNode->nodeName()] = array_map(function (Node\Name $name) {
                    return (string) $name->getLast();
                }, $classLike->implements);
            }

            $properties[$classDiagramNode->nodeName()] = array_map(function (Property $property) {
                return $property->type->getLast();
            }, array_filter($classLike->getProperties(),
                fn(Property $property) => $property->type instanceof FullyQualified)
            );

            $construct = $this->nodeFinder->findFirst($classLike, function (Node $node) {
                return $node instanceof ClassMethod && (string) $node->name === '__construct';
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
        }

        foreach ($nodes as $node) {
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
        }

        return array_values($nodes);
    }

    /**
     * @param string $code
     * @return ClassLike
     */
    private function parseClassLike(string $code): ClassLike
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
        if (!$classLike instanceof ClassLike) {
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
}
