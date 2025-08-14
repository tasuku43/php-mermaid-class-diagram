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
      - `Node/*`: Type representations (`Class_`, `AbstractClass_`, `Interface_`, `Enum_`, `Trait_`) with common abstract `Node`, and the `Nodes` collection.
      - `Node/Relationship/*`: Relationship representations (`Inheritance`, `Realization`, `Composition`, `Dependency`, `TraitUsage`) and the `Relationships` collection.
      - `Node/Connector/*`: Extract relationship information from the AST and connect `Node`s on `Nodes`.
      - `Node/NodeParser`: Uses Finder to collect files, PHP-Parser to build AST, and Connectors to extract relationships.
      - `TraitRenderMode`: Rendering strategy for traits (WithTraits or Flatten).
      - `ClassDiagramDumper`: Debug utility to dump YAML-like structure of nodes/relationships (via reflection).
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
  - `Class_`, `AbstractClass_`, `Interface_`, `Enum_`, `Trait_`
  - `render()`: Outputs Mermaid `class` syntax; stereotypes `<<abstract>>`, `<<interface>>`, `<<enum>>`, `<<trait>>` when applicable.
  - `Nodes`
    - Dictionary-like collection keyed by node name.
    - Provides `add()`, `findByName()`, `getAll()` and `sort()`.
    - `optimize(RenderOptions $options)`: view-time pruning (e.g., hide trait nodes in Flatten mode). Internally uses a private filter.

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
    - Gather `Stmt\ClassLike` (Class/Interface/Enum) and also `Stmt\Trait_` nodes; Class, Interface, Enum, and Trait all become `Node`s.
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
  - `TraitUsageConnector`
    - Records `use TraitName;` for class-like nodes (classes and traits).
    - Enables render-time merging/flattening of trait-derived associations into the using node.

## Rendering
- `ClassDiagram::render()`
  - Applies `Nodes::optimize($options)->sort()` and `Relationships::optimize($options)->sort()`.
  - Emits nodes first, then a blank line, then relationships.
  - Header line `classDiagram`; each subsequent line is indented by 4 spaces.
- Examples
  - Nodes: `class UserService {}` / Interface: `class UserRepositoryInterface { <<interface>> }`
  - Edges: `UserService *-- UserRepositoryInterface: composition`, `UserService ..> User: dependency`

### Render options
- `RenderOptions` controls view-time filtering without changing parsing (all default true via `RenderOptions::default()`):
  - `includeDependencies`: show/hide `Dependency` edges (parameter/return/`new`-derived)
  - `includeCompositions`: show/hide `Composition` edges (properties/constructor promotion/FQCN properties)
  - `includeInheritances`: show/hide `Inheritance` edges (`extends`)
  - `includeRealizations`: show/hide `Realization` edges (`implements`)
  - `traitRenderMode` (`TraitRenderMode`): trait rendering policy (default Flatten).

#### Trait rendering modes
- `WithTraits`:
  - Show trait nodes and `use` edges.
  - Keep trait-origin composition/dependency edges attached to the trait.
  - Suppress duplicate class-level composition/dependency when already provided by a used trait.
- `Flatten`:
  - Hide trait nodes and `use` edges.
  - Reassign trait-origin composition/dependency edges to the using classes.
  - Transitive flattening: handles trait→trait→class chains (reassign to final class users), with deduplication.
  - Apply `include*` filters after optimization.

### Relationships optimization
- `Relationships::optimize(RenderOptions $options)` performs view-time transformations before rendering:
  - Delegates to private implementations per mode:
    - `optimizeWithTraitsInternal()`: keep traits/`use`, suppress class-level duplicates for comp/dep.
    - `optimizeFlattenInternal()`: hide traits/`use`, reassign trait-origin edges to class users (including transitive chains), deduplicate.
  - Applies include flags via private `filterByOptions()`.

### Debug dumper
- `ClassDiagramDumper::toYaml(RenderOptions $options = null): string`
  - Uses reflection to access the diagram’s internals and emits a YAML-like structure for debugging.
  - Mirrors `render()`’s optimize/sort policy so the dump matches what would be rendered under the same options.

## CLI
- Entry: `mermaid-class-diagram`
  - Boots `Symfony\Component\Console\Application` and registers `GenerateCommand`.
- Command: `generate`
  - Option: `--path` (required; file or directory).
  - Option: `--exclude-relationships` (CSV): exclude relationship types: `dependency,composition,inheritance,realization` (aliases supported)
  - Execution: `GenerateCommand::execute()` delegates to `ClassDiagramBuilder`, then writes the Mermaid text to stdout.
  - AST: `ParserFactory::createForVersion(PhpVersion::fromComponents(8, 1))` (PHP 8.1).

## Dependencies
- Runtime
  - `nikic/php-parser` (^4.14 || ^5.0): AST building and traversal.
  - `symfony/finder` (^7.0): File discovery.
  - `symfony/console` (^7.0): CLI framework.
- Dev
  - `phpunit/phpunit`: Unit tests.

## Notes and Trade-offs
- Type resolution simplifications
  - Composition (properties) only considers `FullyQualified` types; non-FQCN and union types are out of scope for now.
  - Dependencies consider `Name` types; `self` is ignored.
- Synthesizing missing references
  - Types referenced but not defined in the scan scope are synthesized as minimal `Node`s so relationships still render.
  - This may produce nodes in the output with empty bodies.
- Traits
  - Participates fully in AST analysis and can be rendered as nodes depending on `TraitRenderMode`.
  - WithTraits: show trait nodes and `use` edges, suppress duplicate class-level comp/dep provided by traits.
  - Flatten: hide trait nodes and `use` edges; reassign trait-origin comp/dep to using classes, including transitive trait chains; deduplicate.
- Safety
  - Never executes target PHP; analysis is static and AST-based.
  - CLI does not currently validate `--path` beyond being required.

## Testing Strategy
- Unit tests
  - Validate node rendering/sorting and relationship extraction (inheritance/realization/composition/dependency).
  - Compare `ClassDiagram`/`ClassDiagramBuilder` output against precise expected strings.
- Fixtures
  - `tests/data/Project/*` provides a small pseudo-app to cover common patterns.
  - `tests/data/TraitChain/*` covers trait→trait chains and flattening behavior.

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
