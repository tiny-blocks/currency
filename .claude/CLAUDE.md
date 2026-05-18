# CLAUDE.md

This is a PHP library in the tiny-blocks ecosystem. Detailed rules live in `.claude/rules/`.
Each file is scoped via its `paths` frontmatter. Read the relevant file before producing or
editing content under its scope.

## Rule files

- `php-library-architecture.md` — folder structure, public API boundary, `Internal/` semantics.
- `php-library-code-style.md` — semantic code rules for `.php` files in `src/` and `tests/`.
- `php-library-commits.md` — Conventional Commits format. Applied only when generating commit messages.
- `php-library-documentation.md` — README and Markdown documentation standards.
- `php-library-github-workflows.md` — CI workflow structure and action pinning.
- `php-library-modeling.md` — nomenclature, value objects, exceptions, enums, complexity.
- `php-library-testing.md` — BDD Given/When/Then, PHPUnit conventions, coverage discipline.
- `php-library-tooling.md` — canonical config files (`composer.json`, `phpcs.xml`, etc).
