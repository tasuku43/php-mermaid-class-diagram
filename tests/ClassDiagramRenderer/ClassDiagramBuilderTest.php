<?php
declare(strict_types=1);

namespace Tasuku43\Tests\MermaidClassDiagram\ClassDiagramRenderer;

use PhpParser\NodeFinder;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PHPUnit\Framework\TestCase;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\ClassDiagramBuilder;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\RenderOptions;
use Tasuku43\MermaidClassDiagram\ClassDiagramRenderer\Node\NodeParser;

class ClassDiagramBuilderTest extends TestCase
{
    private Parser $parser;
    private NodeFinder $nodeFinder;
    private NodeParser $nodeParser;
    private ClassDiagramBuilder $classDigagramBuilder;
    
    protected function setUp(): void
    {
        $this->parser = (new ParserFactory)->createForVersion(PhpVersion::fromComponents(8, 1));
        $this->nodeFinder = new NodeFinder();
        $this->nodeParser = new NodeParser($this->parser, $this->nodeFinder);
        $this->classDigagramBuilder = new ClassDiagramBuilder($this->nodeParser);
    }
    
    public function testBuildFromSampleProject(): void
    {
        $path = __DIR__ . '/../data/Project';
        
        $classDiagram = $this->classDigagramBuilder
            ->build($path)
            ->render(RenderOptions::default());

        $expectedDiagram = <<<'EOT'
classDiagram
    class AbstractController {
        <<abstract>>
    }
    class AuditLogger {
    }
    class AuditTarget {
    }
    class User {
    }
    class UserController {
    }
    class UserRepository {
    }
    class UserRepositoryInterface {
        <<interface>>
    }
    class UserService {
    }
    class UserStatus {
        <<enum>>
    }

    User *-- UserStatus: composition
    AbstractController <|-- UserController: inheritance
    UserController *-- UserService: composition
    UserRepository ..> User: dependency
    UserRepositoryInterface <|.. UserRepository: realization
    UserRepositoryInterface ..> User: dependency
    UserService *-- AuditLogger: composition
    UserService ..> AuditTarget: dependency
    UserService ..> InvalidArgumentException: dependency
    UserService ..> User: dependency
    UserService *-- UserRepositoryInterface: composition

EOT;
        
        $this->assertSame($expectedDiagram, $classDiagram);
    }

    public function testBuildFromSampleClass(): void
    {
        $path = __DIR__ . '/../data/Project/Controller/UserController.php';

        $classDiagram = $this->classDigagramBuilder
            ->build($path)
            ->render(RenderOptions::default());

        $expectedDiagram = <<<'EOT'
classDiagram
    class UserController {
    }

    AbstractController <|-- UserController: inheritance
    UserController *-- UserService: composition

EOT;

        $this->assertSame($expectedDiagram, $classDiagram);
    }

    public function testBuildFromSampleProjectOnlyPropertiesDeps(): void
    {
        $path = __DIR__ . '/../data/Project';

        $classDiagram = $this->classDigagramBuilder
            ->build($path)
            ->render(new RenderOptions(false, true, true, true));

        $expectedDiagram = <<<'EOT'
classDiagram
    class AbstractController {
        <<abstract>>
    }
    class AuditLogger {
    }
    class AuditTarget {
    }
    class User {
    }
    class UserController {
    }
    class UserRepository {
    }
    class UserRepositoryInterface {
        <<interface>>
    }
    class UserService {
    }
    class UserStatus {
        <<enum>>
    }

    User *-- UserStatus: composition
    AbstractController <|-- UserController: inheritance
    UserController *-- UserService: composition
    UserRepositoryInterface <|.. UserRepository: realization
    UserService *-- AuditLogger: composition
    UserService *-- UserRepositoryInterface: composition

EOT;

        $this->assertSame($expectedDiagram, $classDiagram);
    }
}
