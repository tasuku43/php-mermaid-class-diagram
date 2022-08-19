<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node;

use Exception;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\Parser;
use Symfony\Component\Finder\Finder;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Node as ClassDiagramNode;

class NodeBuilder
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
    public function build(string $path): array
    {
        $finder = str_ends_with('.php', $path)
            ? (new Finder())->in(pathinfo($path, PATHINFO_DIRNAME))->name('*.php')->files()
            : (new Finder())->in($path)->name('*.php')->files();

        $nodes      = [];
        $extends    = [];
        $implements = [];

        foreach ($finder as $file) {
            $classLike = $this->parseClassLike($file->getContents());

            $classDiagramNode = match (true) {
                $classLike instanceof Node\Stmt\Class_ => $classLike->isAbstract()
                    ? new AbstractClass_((string)$classLike->name->name)
                    : new Class_((string)$classLike->name->name),
                $classLike instanceof Node\Stmt\Interface_ => new Interface_((string)$classLike->name->name),
                default => throw new Exception('Unexpected match value')
            };

            $nodes[$classDiagramNode->nodeName()] = $classDiagramNode;

            if ($classLike->extends !== null) {
                $extends[(string)$classLike->name] = is_array($classLike->extends)
                    ? array_map(function (Node\Name $name) {
                        return (string) $name->getLast();
                    }, $classLike->extends)
                    : [(string) $classLike->extends->getLast()];
            }
            if (property_exists($classLike, 'implements') && $classLike->implements !== []) {
                $implements[(string)$classLike->name] = array_map(function (Node\Name $name) {
                    return (string) $name->getLast();
                }, $classLike->implements);
            }
        }

        foreach ($extends as $key => $extendsNames) {
            foreach ($extendsNames as $extendsName) {
                $nodes[$key]->extends(
                    $nodes[$extendsName] ?? $this->createDefaultExtendsNode($nodes[$key], $extendsName)
                );
            }
        }
        foreach ($implements as $key => $implementsNames) {
            foreach ($implementsNames as $implementsName) {
                $nodes[$key]->implements($nodes[$implementsName] ??  new Interface_($implementsName));
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
        assert($classLike instanceof ClassLike);

        return $classLike;
    }

    private function createDefaultExtendsNode(ClassDiagramNode $extended, string $extendsNodeName): ClassDiagramNode
    {
        return match (true) {
            $extended instanceof Class_ => new Class_($extendsNodeName),
            $extended instanceof Interface_ => new Interface_($extendsNodeName)
        };
    }
}
