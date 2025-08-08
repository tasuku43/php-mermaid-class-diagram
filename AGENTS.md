# Repository Guidelines

## Project Structure and Modules
- Source: `src/` (PSR-4 `Tasuku43\MermaidClassDiagram\...`). See `ARCHITECTURE.md` for architecture and module details.
- CLI: `mermaid-class-diagram` (also runnable via `vendor/bin/mermaid-class-diagram`).
- Meta files: `composer.json` (autoload/deps), `composer.lock`, `vendor/`.

## Build, Test, and Development
- Install deps: `composer install`
- Run tests:
  - `vendor/bin/phpunit -c tests/phpunit.xml.dist`
  - or `vendor/bin/phpunit tests`
- Try the CLI:
  - Directory: `vendor/bin/mermaid-class-diagram generate --path src`
  - Single file: `vendor/bin/mermaid-class-diagram generate --path src/SomeClassA.php`

## Coding Standards and Naming
- Standard: PSR-12, 4-space indentation. Prefer strict types, typed returns/properties where possible.
- Naming: Classes in StudlyCaps; methods/properties in camelCase. Namespace `Tasuku43\MermaidClassDiagram\...`.
- Autoloading (PSR-4): Configure via Composer `autoload.psr-4`. This repo maps `Tasuku43\MermaidClassDiagram\` to `src/`, so `Tasuku43\MermaidClassDiagram\Foo\Bar` resides at `src/Foo/Bar.php`.
- Organization: Place Console commands under `Console/Command`; keep options consistent (e.g., `--path`).
- Design: Keep the public API minimal; renderer small and functionally oriented.

## Testing Guidelines
- Framework: PHPUnit 9.5.
- Layout: Place `*Test.php` in `tests/`, mirroring `src/` structure when practical.
- Execution: `vendor/bin/phpunit -c tests/phpunit.xml.dist`.
- Targets: Node analysis; detection of relationships (inheritance/realization/dependency/composition); verification of Mermaid output lines.
- Fixtures: Use `tests/data/` for sample inputs.

## Commit and PR Guidelines
- Commits: Prefer Conventional Commits (e.g., `feat:`, `fix:`, `refactor:`, `test:`, `build(deps):`).
- PRs should include:
  - Purpose/context with related issue links.
  - Scope of changes and whether there are breaking changes.
  - CLI output and Mermaid before/after when relevant.

## Security and Configuration Tips
- No runtime secrets required. Never execute the target PHP; static analysis only.
- Validate `--path` input and avoid side effects during analysis.
