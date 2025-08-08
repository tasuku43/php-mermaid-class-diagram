# Architecture Overview (php-mermaid-class-diagram)

## Goals and Principles
- Purpose: Generate Mermaid class diagrams (classDiagram) from PHP source code.
- Focus: Omit class internals as much as possible and accurately extract only “nodes (types)” and “relationships (inheritance, realization, composition, dependency)”.
- Design: Small responsibilities, clear separation between parsing and rendering, and a preference for pure transformations.

## Directory Layout and Separation of Concerns
- `src/`
  - `ClassDiagramRenderer/`
    - Holds models for nodes and relationships, AST-based analysis (Connector classes), and rendering logic.
    - Key classes:
      - `ClassDiagram`: Aggregates nodes and relationships; renders Mermaid text.
      - `ClassDiagramBuilder`: Facade to parse and assemble nodes/relationships.
      - `Node/*`: Type representations (`Class_`, `AbstractClass_`, `Interface_`, `Enum_`) and the common abstract `Node`, plus the `Nodes` collection.
      - `Node/Relationship/*`: Relationship representations (`Inheritance`, `Realization`, `Composition`, `Dependency`).
      - `Node/Connector/*`: Extract relationship information from the AST and connect `Node`s on `Nodes`.
      - `Node/NodeParser`: Uses Finder to collect files, PHP-Parser to build AST, and Connectors to extract relationships.
  - `Console/Command/GenerateCommand.php`: Symfony Console command (`generate --path`).
- `mermaid-class-diagram`: Executable binary that boots the Console app.
- `tests/`: PHPUnit tests and fixtures (`tests/data/Project/*`).

## Domain Model
### Nodes (types)
- `Node` (abstract)
  - Minimal representation of a type with `$name`.
  - Holds source collections for relationships:
    - `$extends`, `$implements`, `$properties` (composition), `$depends` (dependency)
  - `relationships()`: Builds an array of `Relationship` objects from the above collections.
    - De-duplicates and filters: removes conflicts (e.g., dependency also present as composition/inheritance/realization) and self references.
  - `sortNodes(array &$nodes)`: Sorts by node name (ascending).
- Concrete types
  - `Class_`, `AbstractClass_`, `Interface_`, `Enum_`
  - `render()`: Outputs Mermaid `class` syntax; stereotypes `<<abstract>>`, `<<interface>>`, `<<enum>>` when applicable.
- `Nodes`
  - Dictionary-like collection keyed by node name.
  - Provides `add()`, `findByName()`, `getAllNodes()`.

### Relationships
- `Relationship` (abstract): Holds `from` and `to`; `render()` outputs Mermaid edges.
  - `sortRelationships(array &$relationships)`: Sorts by `from to` (ascending).
- Concrete relationships and Mermaid syntax
  - `Inheritance`: `Parent <|-- Child: inheritance`
  - `Realization`: `Interface <|.. Class: realization`
  - `Composition`: `Owner *-- Part: composition`
  - `Dependency`: `From ..> To: dependency`

## Analysis Pipeline (AST → Nodes/Relationships)
- Entry point: `ClassDiagramBuilder::build(string $path)`
  1) Call `NodeParser::parse($path)` to get `Nodes`.
  2) For each `Node`, collect `relationships()` into `ClassDiagram`.
- `NodeParser`
  - Input: `--path` may be a directory (glob `*.php`) or a single file (`Symfony\Component\Finder\Finder`).
  - Preparation: Build AST with `PhpParser`; run `NameResolver` and `ParentConnectingVisitor`.
  - Class-like detection:
    - Gather `Stmt\ClassLike` (Class/Interface/Enum) and also `Stmt\Trait_` nodes, but only Class/Interface/Enum become `Node`s.
    - If no valid class-like found in a file, skip via `CannnotParseToClassLikeException`.
  - Two-phase processing:
    - Phase 1: Create `Node` (`Class_`/`AbstractClass_`/`Interface_`/`Enum_`) for each valid class-like and add to `Nodes`.
    - Phase 2: Build Connector instances, then call `connect()` to wire up relationships.
- Connectors (AST → edges on `Nodes`)
  - `InheritanceConnector`
    - Extracts `extends` (Interface→Interface, otherwise Class→Class).
    - Missing referenced types are synthesized (`Interface_` or `Class_`).
  - `RealizationConnector`
    - Extracts `implements`; synthesizes missing interfaces as `Interface_`.
  - `CompositionConnector`
    - Treats constructor property promotion params with visibility and type `Name` as composition.
    - Treats properties whose type is `FullyQualified` as composition.
  - `DependencyConnector`
    - Method params typed as `Name` with flags===0 (non-promotion) are dependencies.
    - Method return types of `Name` are dependencies.
    - `new` expressions (`Expr\New_`) add dependencies.
    - Filters out `self` and de-duplicates.

## Rendering
- `ClassDiagram::render()`
  - Sorts nodes by name; sorts relationships by `from to`.
  - Emits nodes first, then a blank line, then relationships.
  - Header line `classDiagram`; each subsequent line is indented by 4 spaces.
- Examples
  - Nodes: `class UserService {}` / Interface: `class UserRepositoryInterface { <<interface>> }`
  - Edges: `UserService *-- UserRepositoryInterface: composition`, `UserService ..> User: dependency`

## CLI
- Entry: `mermaid-class-diagram`
  - Boots `Symfony\Component\Console\Application` and registers `GenerateCommand`.
- Command: `generate`
  - Option: `--path` (required; file or directory).
  - Execution: `GenerateCommand::execute()` delegates to `ClassDiagramBuilder`, then writes the Mermaid text to stdout.
  - AST: `ParserFactory::createForVersion(PhpVersion::fromComponents(8, 1))` (PHP 8.1).

## Dependencies
- Runtime
  - `nikic/php-parser` (^4.14 || ^5.0): AST building and traversal.
  - `symfony/finder` (^7.0): File discovery.
  - `symfony/console` (^7.0): CLI framework.
- Dev
  - `phpunit/phpunit` (^9.5): Unit tests.

## Notes and Trade-offs
- Type resolution simplifications
  - Composition (properties) only considers `FullyQualified` types; non-FQCN and union types are out of scope for now.
  - Dependencies consider `Name` types; `self` is ignored.
- Synthesizing missing references
  - Types referenced but not defined in the scan scope are synthesized as minimal `Node`s so relationships still render.
  - This may produce nodes in the output with empty bodies.
- Traits
  - Traits are detected in AST collection but not rendered as nodes; only Class/Interface/Enum are supported.
- Safety
  - Never executes target PHP; analysis is static and AST-based.
  - CLI does not currently validate `--path` beyond being required.

## Testing Strategy
- Unit tests
  - Validate node rendering/sorting and relationship extraction (inheritance/realization/composition/dependency).
  - Compare `ClassDiagram`/`ClassDiagramBuilder` output against precise expected strings.
- Fixtures
  - `tests/data/Project/*` provides a small pseudo-app to cover common patterns.

## Extensibility
- New relationships
  - Add a `Relationship` subclass and corresponding Connector `parse()/connect()`; register it in `NodeParser::$connectorParsers`.
- New node kinds
  - Add a `Node` subclass and extend `NodeParser::createClassDiagramNodeFromClassLike()`.
- Extraction rules
  - Adjust AST predicates in `CompositionConnector`/`DependencyConnector` to widen/narrow detection.

## Flow Summary
1) Discover PHP files with Finder.
2) Build and normalize AST with PHP-Parser.
3) Register Class/Interface/Enum nodes in `Nodes`.
4) Connectors extract relationships from AST and wire nodes in `Nodes`.
5) `ClassDiagramBuilder` aggregates and `ClassDiagram::render()` prints Mermaid.

---
This document exists to make future changes faster by clarifying where responsibilities live and how to extend the system safely.
