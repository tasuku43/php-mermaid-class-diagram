<?php
declare(strict_types=1);

namespace Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node;

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
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\Connector\DependencyConnector;
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
            fn(Stmt\Interface_|Stmt\Class_|Stmt\Enum_|Stmt\Trait_ $classLike, ClassDiagramNode $classDiagramNode) => InheritanceConnector::parse($classLike, $classDiagramNode),
            fn(Stmt\Interface_|Stmt\Class_|Stmt\Enum_|Stmt\Trait_ $classLike, ClassDiagramNode $classDiagramNode) => RealizationConnector::parse($classLike, $classDiagramNode),
            fn(Stmt\Interface_|Stmt\Class_|Stmt\Enum_|Stmt\Trait_ $classLike, ClassDiagramNode $classDiagramNode) => CompositionConnector::parse($nodeFinder, $classLike, $classDiagramNode),
            fn(Stmt\Interface_|Stmt\Class_|Stmt\Enum_|Stmt\Trait_ $classLike, ClassDiagramNode $classDiagramNode) => DependencyConnector::parse($nodeFinder, $classLike, $classDiagramNode),
        ];
    }

    /**
     * @param string $path
     *
     * @return Nodes
     *
     * @throws Exception
     */
    public function parse(string $path): Nodes
    {
        $finder = str_ends_with($path, '.php')
            ? (new Finder())->in(pathinfo($path, PATHINFO_DIRNAME))->name(pathinfo($path, PATHINFO_BASENAME))->files()
            : (new Finder())->in($path)->name('*.php')->files();

        $nodes = new Nodes();
        $connectors = [];

        foreach ($finder as $file) {
            try {
                // Get all class-like declarations from the file
                $classLikes = $this->parseClassLikes($file->getContents());
                
                foreach ($classLikes as $classLike) {
                    $classDiagramNode = $this->createClassDiagramNodeFromClassLike($classLike);
                    
                    $nodes->add($classDiagramNode);
                    
                    foreach ($this->connectorParsers as $connectorParser) {
                        $connectors[] = $connectorParser($classLike, $classDiagramNode);
                    }
                }
            } catch (CannnotParseToClassLikeException) {
                continue;
            }
        }

        foreach ($connectors as $connector) {
            $connector->connect($nodes);
        }

        return $nodes;
    }

    /**
     * Parse all class-like declarations in code
     * 
     * @param string $code
     * @return array<Stmt\Class_|Stmt\Interface_|Stmt\Enum_|Stmt\Trait_>
     * @throws CannnotParseToClassLikeException
     */
    private function parseClassLikes(string $code): array
    {
        $nodeTraverser = new NodeTraverser;
        $nodeTraverser->addVisitor(new NameResolver);
        $nodeTraverser->addVisitor(new ParentConnectingVisitor());

        $ast = $this->parser->parse($code);
        $ast = $nodeTraverser->traverse($ast);

        // Find all class-like nodes
        $classLikes = $this->nodeFinder->findInstanceOf($ast, Stmt\ClassLike::class);
        
        // Find traits (not part of ClassLike in PHP-Parser)
        $traits = $this->nodeFinder->findInstanceOf($ast, Stmt\Trait_::class);
        
        // Combine all nodes
        $allClassLikes = array_merge($classLikes, $traits);
        
        // Filter to only include supported types
        $validClassLikes = array_filter($allClassLikes, function ($node) {
            return $node instanceof Stmt\Class_ 
                || $node instanceof Stmt\Interface_
                || $node instanceof Stmt\Enum_;
        });
        
        if (empty($validClassLikes)) {
            throw new CannnotParseToClassLikeException();
        }

        return $validClassLikes;
    }

    /**
     * @throws Exception
     */
    private function createClassDiagramNodeFromClassLike($classLike): ClassDiagramNode
    {
        return match (true) {
            $classLike instanceof Stmt\Class_ => $classLike->isAbstract()
                ? new AbstractClass_((string)$classLike->name->name)
                : new Class_((string)$classLike->name->name),
            $classLike instanceof Stmt\Interface_ => new Interface_((string)$classLike->name->name),
            $classLike instanceof Stmt\Enum_ => new Enum_((string)$classLike->name->name),
            default => throw new Exception('Unexpected match value: ' . get_class($classLike))
        };
    }
}
