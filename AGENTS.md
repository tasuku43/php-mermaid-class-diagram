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
 - Follow the PR template in `.github/pull_request_template.md` when opening a PR.

## Security and Configuration Tips
- No runtime secrets required. Never execute the target PHP; static analysis only.
- Validate `--path` input and avoid side effects during analysis.

## Agent Workspace (`.codex/`)
- Purpose: A git-ignored workspace at the project root for agent output. Use it freely for investigation notes, scratch files, and work plans.
- Location: `./.codex/` (already listed in `.gitignore`). Do not commit contents.
- Suggested structure:
  - `/.codex/plans/`: Work plans, TODOs, execution checklists.
  - `/.codex/analysis/`: Investigation notes, findings, benchmarks, quick experiments.
  - `/.codex/out/`: Generated artifacts for manual inspection (e.g., temporary diagrams, logs).
  - `/.codex/cache/`: Ephemeral cache or intermediate data.
- Naming convention: `YYYYMMDD-HHMMSS-topic.md` (e.g., `20250809-1542-parser-investigation.md`).
- Usage policy:
  - Output investigation results and work plans to `.codex/` instead of the tracked repo.
  - Keep outputs self-contained and disposable; no assumptions of stability.
  - Do not store secrets; avoid side effects beyond this directory.
