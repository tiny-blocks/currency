---
description: Standards for README and other public-facing Markdown docs in PHP libraries.
paths:
    - "**/*.md"
---

# Documentation

Standards for `README.md` and other public-facing Markdown files in the repository. PHPDoc rules
for `.php` files live in `php-library-code-style.md`. American English applies everywhere (see
the American English section in `php-library-code-style.md`).

The `CONTRIBUTING.md` file is centralized at
`https://github.com/tiny-blocks/tiny-blocks/blob/main/CONTRIBUTING.md`. Each library's README and
pull request template link to that location. No local `CONTRIBUTING.md` is created per library.

## Pre-output checklist

Verify every item before producing any Markdown documentation. If any item fails, revise before
outputting.

1. README title is `# <LibName>` with spaces between words (`# Building Blocks`, not
   `# BuildingBlocks`).
2. License badge is the only badge. No build, coverage, Packagist, or version badges.
3. Header is followed by an anchor-linked table of contents.
4. Table of contents uses `*` for top-level (H2) entries, `+` indented by 4 spaces for
   second-level (H3) entries, and `-` indented by 8 spaces for third-level (H4) entries. Every
   heading from the document appears in the TOC, except FAQ entries: the FAQ is represented by
   a single `* [FAQ](#faq)` line regardless of how many questions it contains.
5. Sections appear in the canonical order: Overview, Installation, How to use, FAQ (optional),
   License, Contributing.
6. FAQ exists only when there are genuine points of confusion or unusual design decisions. Skip
   it entirely when not needed.
7. **Self-contained code examples** are blocks that include any of: a `use` statement, a
   `class`/`enum`/`interface`/`trait`/`function` declaration, or more than 3 lines of
   executable code. Self-contained blocks open with `<?php`, a blank line, `declare(strict_types=1);`,
   and every `use` statement required to compile.
8. **Inline fragment examples** are blocks with at most 3 lines of executable code, no `use`
   statements, and no type declarations. Fragments may omit the prologue.
9. Inline comments in PHP code examples (inside Markdown files) use `#` for single-line.
   Multi-line comments use consecutive `#` lines, all aligned at the same indentation level.
   `//` and `/* */` are not used in examples.
10. Tables are used for structured data such as constructor parameter lists, builder method
    catalogs, configuration options, or complexity tables. Column layout is chosen per case.
11. FAQ entries use the heading format `### NN. <Question>?` with zero-padded numbering
    (`### 01.`, `### 02.`).
12. FAQ bibliographic citations use the format
    `> Author, *Title* (Publisher, Year), Chapter X, "Section Name".`
13. License and Contributing sections each follow the canonical one-line template.
14. Repository includes `SECURITY.md`, `.github/ISSUE_TEMPLATE/bug_report.md`,
    `.github/ISSUE_TEMPLATE/feature_request.md`, and `.github/PULL_REQUEST_TEMPLATE.md`, each
    matching the canonical template in "Other documentation files".

## README

### Structure

The README follows a fixed section order:

1. **Overview**. One or more paragraphs explaining the problem the library solves and its design
   philosophy. Cross-references to related `tiny-blocks` libraries belong here.
2. **Installation**. Composer command in a code block, with no surrounding prose unless strictly
   necessary.
3. **How to use**. Runnable examples covering the primary use cases. Each subsection demonstrates
   one capability with a heading and a self-contained code block.
4. **FAQ** (optional). Numbered questions that address real points of confusion or unusual design
   decisions.
5. **License**. One-line link to the `LICENSE` file.
6. **Contributing**. One-line link to the centralized `CONTRIBUTING.md` in
   `tiny-blocks/tiny-blocks`.

### Header and license badge

The first line is `# <LibName>` followed by a blank line and the license badge:

```markdown
# Outbox

[![License](https://img.shields.io/badge/license-MIT-green)](https://github.com/tiny-blocks/<lib-name>/blob/main/LICENSE)
```

Replace `<lib-name>` with the library's repository name. The badge is the only badge in the document.

### Table of contents

The table of contents is anchor-linked. Top-level (H2) entries use `*`. Second-level (H3)
entries use `+` indented by 4 spaces. Third-level (H4) entries use `-` indented by 8 spaces.
Every heading from the document appears, with one exception: the FAQ is represented by a single
`* [FAQ](#faq)` line. Its questions never appear as TOC sub-entries, regardless of how many
exist.

```markdown
* [Overview](#overview)
* [Installation](#installation)
* [How to use](#how-to-use)
    + [Subtopic A](#subtopic-a)
    + [Subtopic B](#subtopic-b)
* [FAQ](#faq)
* [License](#license)
* [Contributing](#contributing)
```

Use the third level whenever the document has H4 headings, regardless of whether they form a
two-axis split. The TOC mirrors the document structure exactly.

```markdown
* [How to use](#how-to-use)
    + [Entity](#entity)
        - [Single-field identity](#single-field-identity)
        - [Compound identity](#compound-identity)
    + [Aggregate](#aggregate)
```

### Code examples

Code examples fall into two categories.

**Self-contained examples** include at least one of:

- A `use` statement.
- A `class`, `enum`, `interface`, `trait`, or `function` declaration.
- More than 3 lines of executable code.

They open with `<?php`, a blank line, and `declare(strict_types=1);`. Every `use` statement
required to compile is present. A reader can copy the block into a file and run it.

```php
<?php

declare(strict_types=1);

use TinyBlocks\Outbox\DoctrineOutboxRepository;
use TinyBlocks\Outbox\Serialization\PayloadSerializerReflection;
use TinyBlocks\Outbox\Serialization\PayloadSerializers;

# Single-line comments use #.
$repository = new DoctrineOutboxRepository(
    connection: $connection,
    payloadSerializers: PayloadSerializers::createFrom(elements: [
        new PayloadSerializerReflection()
    ])
);

# Multi-line comments use consecutive # lines,
# all aligned at the same indentation level.
# `//` and `/* */` are not used in examples.
$repository->push(records: $order->recordedEvents());
```

**Inline fragment examples** have all of:

- At most 3 lines of executable code.
- No `use` statements.
- No type declarations.

Fragments may omit the prologue.

```php
Code::OK->value;
```

The criteria are mechanical: a block that meets any self-contained condition gets the prologue. A block that meets every fragment condition may omit it. There is no middle ground.

The `#` convention for inline comments applies only to code examples inside Markdown files. PHP
files under `src/` and `tests/` have no inline comments at all, except `# TODO: <reason>` (see
item 16 in `php-library-code-style.md`).

### FAQ

FAQ entries are numbered with zero-padded prefixes and end with a question mark:

```markdown
### 01. Why is DomainEvent close to a marker interface?

A domain event is a fact about something that happened in the domain. The contract carries only
`revision()` so the library can route schema migrations through upcasters. Everything else
(aggregate identity, sequence number, aggregate type, occurrence timestamp) is envelope metadata
that belongs to `EventRecord`.

> Vaughn Vernon, *Implementing Domain-Driven Design* (Addison-Wesley, 2013), Chapter 8,
> "Domain Events".
```

Bibliographic citations follow the format
`> Author, *Title* (Publisher, Year), Chapter X, "Section Name".` The chapter and section
fragments are optional when the title is precise enough on its own. Multiple citations can be
stacked as separate blockquote lines.

### License and Contributing

The License section is a single line:

```markdown
## License

<LibName> is licensed under [MIT](LICENSE).
```

The Contributing section is a single line pointing to the centralized guideline:

```markdown
## Contributing

Please follow the [contributing guidelines](https://github.com/tiny-blocks/tiny-blocks/blob/main/CONTRIBUTING.md) to
contribute to the project.
```

## Structured data

Tables are preferred to prose for any structured information: constructor parameter lists,
builder method catalogs, default value tables, complexity tables, and configuration matrices.
Column layout is chosen per case. No fixed column set is mandated.

## Other documentation files

Every library repository includes the following files in addition to the README. Each follows
the canonical template below.

### SECURITY.md

```markdown
# Security Policy

## Supported versions

Only the latest release receives security updates.

## Reporting a vulnerability

Report security vulnerabilities privately via
[GitHub Security Advisories](https://github.com/tiny-blocks/<lib-name>/security/advisories/new).

Please do not disclose the vulnerability publicly until it has been addressed.
```

Replace `<lib-name>` with the repository name.

### .github/ISSUE_TEMPLATE/bug_report.md

```markdown
---
name: Bug report
about: Report a bug to help improve the library
labels: bug
---

## Description

A clear and concise description of the bug.

## Steps to reproduce

1.
2.
3.

## Expected behavior

What should happen.

## Actual behavior

What actually happens.

## Environment

- PHP version:
- Library version:
- OS:
```

### .github/ISSUE_TEMPLATE/feature_request.md

```markdown
---
name: Feature request
about: Suggest a feature for the library
labels: enhancement
---

## Problem

What problem does this feature solve?

## Proposed solution

How should the feature work?

## Alternatives considered

Other approaches considered.
```

### .github/PULL_REQUEST_TEMPLATE.md

```markdown
> Please follow the [contributing guidelines](https://github.com/tiny-blocks/tiny-blocks/blob/main/CONTRIBUTING.md).

## Summary

What this pull request does.

## Related issue

Closes #...

## Checklist

- [ ] Tests added or updated.
- [ ] Documentation updated when applicable.
- [ ] `composer review` passes.
- [ ] `composer tests` passes.
```
