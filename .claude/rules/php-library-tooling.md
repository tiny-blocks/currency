---
description: Canonical config files for PHP libraries in the tiny-blocks ecosystem.
paths:
    - "composer.json"
    - "phpcs.xml"
    - "phpstan.neon.dist"
    - "phpunit.xml"
    - "infection.json.dist"
    - ".editorconfig"
    - ".gitattributes"
    - ".gitignore"
    - "Makefile"
---

# Tooling

Canonical configuration files for a PHP library in the tiny-blocks ecosystem. Each file has a
fixed shape. Deviations require justification. Folder structure lives in
`php-library-architecture.md`. Code style lives in `php-library-code-style.md`.

## Pre-output checklist

Verify every item before creating, editing, or relocating any of the files below. If any item
fails, revise before outputting.

1. The library repository contains all the following files at its root: `composer.json`,
   `phpcs.xml`, `phpstan.neon.dist`, `phpunit.xml`, `infection.json.dist`, `.editorconfig`,
   `.gitattributes`, `.gitignore`, `Makefile`.
2. `composer.json` exposes exactly five scripts: `configure`, `configure-and-update`, `review`,
   `test-file`, `tests`. No other public scripts are defined.
3. `composer.json` fixed fields use the canonical values defined in the "composer.json" section
   (`license`, `type`, `minimum-stability`, `prefer-stable`, `authors`, `config`, `require.php`).
4. `composer.json` `description` is a single short sentence describing what the library does.
   Multi-sentence or multi-paragraph descriptions belong in the README Overview, not in Composer
   metadata.
5. `composer.json` includes a `keywords` array. The first keyword is always `"tiny-blocks"`.
   Additional keywords are topic tokens derived from the library's purpose (`psr-7`,
   `http-client`, `event-sourcing`, etc.).
6. `phpcs.xml` references only the `PSR12` ruleset. No additional sniffs are added.
7. `phpunit.xml` sets all five `failOn*` flags to `true`: `failOnDeprecation`, `failOnNotice`,
   `failOnPhpunitDeprecation`, `failOnRisky`, `failOnWarning`.
8. `phpunit.xml` sets `executionOrder="random"` and `beStrictAboutOutputDuringTests="true"`.
9. `infection.json.dist` sets `minMsi: 100` and `minCoveredMsi: 100`. Lowering either value is
   prohibited.
10. `.editorconfig` sets `max_line_length = 120`, `indent_size = 4`, `indent_style = space`, and
    `end_of_line = lf` for PHP files. YAML uses `indent_size = 2`. Makefile uses `indent_style = tab`.
11. `.gitattributes` sets `* text=auto eol=lf` and lists every dev-only file under `export-ignore`.
    The Packagist tarball contains only `src/`, `composer.json`, `README.md`, and `LICENSE`.
    `.claude/` is listed under `export-ignore` (versioned on GitHub for contributor parity,
    excluded from the published package).
12. `.gitignore` follows the canonical content in the ".gitignore" section. `.claude/` is **not**
    listed (it is versioned on GitHub).
13. `Makefile` wraps every PHP and Composer command in a Docker container using the canonical
    image `gustavofreze/php:8.5-alpine`. No PHP command runs on the host directly.
14. All test artifact paths use `reports/` (plural). The directory is consistent across
    `composer tests`, `infection.json.dist`, `phpunit.xml`, and `Makefile`.
15. The `reports/` directory is listed under `export-ignore` in `.gitattributes`.

## composer.json

Fixed fields, identical in every library: `license`, `type`, `minimum-stability`, `prefer-stable`,
`require.php`, `authors`, `config.allow-plugins`, `config.sort-packages`, `scripts`, and the five
universal dev dependencies (`ergebnis/composer-normalize`, `infection/infection`, `phpstan/phpstan`,
`phpunit/phpunit`, `squizlabs/php_codesniffer`).

Per-library fields, vary by library: `name`, `description`, `keywords`, `homepage`, `support`,
`autoload`, `autoload-dev`. The `require-dev` section may add libraries needed by tests (for
example, HTTP client implementations in a PSR-7 library) on top of the five universal tools.

```json
{
    "name": "tiny-blocks/<lib-name>",
    "description": "<one short sentence describing what the library does>",
    "license": "MIT",
    "type": "library",
    "keywords": [
        "tiny-blocks",
        "<topic-1>",
        "<topic-2>"
    ],
    "authors": [
        {
            "name": "Gustavo Freze de Araujo Santos",
            "homepage": "https://github.com/gustavofreze"
        }
    ],
    "homepage": "https://github.com/tiny-blocks/<lib-name>",
    "support": {
        "issues": "https://github.com/tiny-blocks/<lib-name>/issues",
        "source": "https://github.com/tiny-blocks/<lib-name>"
    },
    "require": {
        "php": "^8.5"
    },
    "require-dev": {
        "ergebnis/composer-normalize": "^2.51",
        "infection/infection": "^0.32",
        "phpstan/phpstan": "^2.1",
        "phpunit/phpunit": "^13.1",
        "squizlabs/php_codesniffer": "^4.0"
    },
    "minimum-stability": "stable",
    "prefer-stable": true,
    "autoload": {
        "psr-4": {
            "TinyBlocks\\<LibName>\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Test\\TinyBlocks\\<LibName>\\": "tests/"
        }
    },
    "config": {
        "allow-plugins": {
            "ergebnis/composer-normalize": true,
            "infection/extension-installer": true
        },
        "sort-packages": true
    },
    "scripts": {
        "configure": [
            "@composer install --optimize-autoloader",
            "@composer normalize"
        ],
        "configure-and-update": [
            "@composer update --optimize-autoloader",
            "@composer normalize"
        ],
        "review": [
            "@php ./vendor/bin/phpcs --standard=phpcs.xml --extensions=php ./src ./tests",
            "@php ./vendor/bin/phpstan analyse -c phpstan.neon.dist --quiet --no-progress"
        ],
        "test-file": "@php ./vendor/bin/phpunit --configuration phpunit.xml --no-coverage --filter",
        "tests": [
            "@php -d memory_limit=2G ./vendor/bin/phpunit --configuration phpunit.xml tests",
            "@php ./vendor/bin/infection --threads=max --logger-html=reports/coverage/mutation-report.html --coverage=reports/coverage"
        ]
    }
}
```

Script usage:

- `composer configure` runs `composer install --optimize-autoloader` followed by `composer normalize`.
  Use this after cloning the repository or pulling new changes.
- `composer configure-and-update` runs `composer update --optimize-autoloader` followed by
  `composer normalize`. Use this when intentionally updating dependencies.
- `composer review` runs `phpcs` and `phpstan` in sequence. Used by CI and local validation.
- `composer tests` runs `phpunit` followed by `infection`. Used by CI.
- `composer test-file <FilterPattern>` runs a filtered subset of tests without coverage. Local
  development only.

## phpcs.xml

References only the `PSR12` ruleset. Additional formatting rules (vertical alignment, trailing
comma, etc.) live in `php-library-code-style.md` under "Formatting overrides".

```xml
<?xml version="1.0"?>
<ruleset name="tiny-blocks">
    <description>Code style for the tiny-blocks library.</description>
    <rule ref="PSR12"/>
    <file>src</file>
    <file>tests</file>
</ruleset>
```

## phpstan.neon.dist

Static analysis configuration. Runs at the highest level on both `src/` and `tests/`. Invoked
by the `review` Composer script.

```neon
parameters:
    level: max
    paths:
        - src
        - tests
    reportUnmatchedIgnoredErrors: true
```

`ignoreErrors` is permitted to suppress legitimate false positives produced by `level: max`
(third-party type signatures with `mixed`, PHP-FIG interfaces returning untyped arrays, trait
unused-method warnings on shared behavior, etc.). Each entry follows these rules:

- A short comment above the entry justifies its existence.
- Prefer scoping via `identifier:` plus `path:` over raw `#...#` message patterns.
- `reportUnmatchedIgnoredErrors: true` is mandatory. Obsolete entries fail the build, forcing
  cleanup.

Example with `ignoreErrors`:

```neon
parameters:
    level: max
    paths:
        - src
        - tests
    ignoreErrors:
        # Trait method intentionally unused by the consuming aggregate; reflection wires it.
        - identifier: trait.unused
          path: src/Internal/EventualAggregateRootBehavior.php

        # json_encode signature carries `mixed` for backward compatibility at level max.
        - identifier: argument.type
          path: src/Internal/Serialization/JsonEncoder.php
    reportUnmatchedIgnoredErrors: true
```

## phpunit.xml

Strict configuration. All `failOn*` flags are `true`. `executionOrder="random"` forces tests to be
independent of one another. Coverage and JUnit reports go under `reports/`.

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="./vendor/phpunit/phpunit/phpunit.xsd"
         beStrictAboutOutputDuringTests="true"
         cacheDirectory=".phpunit.cache"
         colors="true"
         executionOrder="random"
         failOnDeprecation="true"
         failOnNotice="true"
         failOnPhpunitDeprecation="true"
         failOnRisky="true"
         failOnWarning="true">

    <source>
        <include>
            <directory>src</directory>
        </include>
    </source>

    <testsuites>
        <testsuite name="default">
            <directory>tests</directory>
        </testsuite>
    </testsuites>

    <coverage>
        <report>
            <xml outputDirectory="reports/coverage/coverage-xml"/>
            <html outputDirectory="reports/coverage/coverage-html"/>
            <text outputFile="reports/coverage.txt"/>
            <clover outputFile="reports/coverage-clover.xml"/>
        </report>
    </coverage>

    <logging>
        <junit outputFile="reports/coverage/junit.xml"/>
    </logging>

</phpunit>
```

Root attributes are sorted alphabetically.

## infection.json.dist

Mutation testing configuration. `minMsi` and `minCoveredMsi` are both `100`. Mutants that escape
make the build fail.

```json
{
    "logs": {
        "text": "reports/infection/logs/infection-text.log",
        "summary": "reports/infection/logs/infection-summary.log"
    },
    "tmpDir": "reports/infection/",
    "minMsi": 100,
    "timeout": 30,
    "source": {
        "directories": [
            "src"
        ]
    },
    "phpUnit": {
        "configDir": "",
        "customPath": "./vendor/bin/phpunit"
    },
    "mutators": {
        "@default": true
    },
    "minCoveredMsi": 100,
    "testFramework": "phpunit"
}
```

## .editorconfig

Whitespace and line ending rules applied by editor integrations.

```ini
root = true

[*]
charset = utf-8
end_of_line = lf
indent_size = 4
indent_style = space
max_line_length = 120
insert_final_newline = true
trim_trailing_whitespace = true

[*.{yml,yaml}]
indent_size = 2

[Makefile]
indent_style = tab

[*.md]
trim_trailing_whitespace = false
```

## .gitattributes

Normalizes line endings to LF and excludes every dev-only file from the Packagist tarball. The
published package contains only `src/`, `composer.json`, `README.md`, and `LICENSE`.

```
* text=auto eol=lf

*.php text diff=php

# Dev-only, excluded from the Packagist tarball
/.github             export-ignore
/tests               export-ignore
/.claude             export-ignore
/.editorconfig       export-ignore
/.gitattributes      export-ignore
/.gitignore          export-ignore
/phpunit.xml         export-ignore
/phpunit.xml.dist    export-ignore
/phpstan.neon        export-ignore
/phpstan.neon.dist   export-ignore
/phpcs.xml           export-ignore
/phpcs.xml.dist      export-ignore
/infection.json      export-ignore
/infection.json.dist export-ignore
/Makefile            export-ignore
/CONTRIBUTING.md     export-ignore
/CHANGES.md          export-ignore
/reports             export-ignore
/.phpunit.cache      export-ignore
```

## .gitignore

Keeps the repository working tree clean of artifacts that should never be committed. Entries
are grouped from most fundamental (PHP dependencies) to least critical (OS files). The
`.claude/` directory is **not** listed here. It is versioned on GitHub so other contributors
share the same rules, and it is excluded from the published Packagist tarball through
`export-ignore` in `.gitattributes` (see above).

```
# PHP dependencies
/vendor/
composer.lock

# Tooling cache
.phpcs-cache
.phpunit.cache/
.php-cs-fixer.cache
.phpunit.result.cache

# Coverage and reports
build/
reports/
coverage/
infection.log

# Editors and agents
.idea/
.cursor/
.vscode/

# OS
Thumbs.db
.DS_Store
Desktop.ini
```

## Makefile

Thin wrapper over Composer scripts. Every PHP and Composer command runs inside a Docker container
using the canonical image `gustavofreze/php:8.5-alpine`. Targets that match a Composer script
delegate to it directly, avoiding duplication.

```makefile
PWD := $(CURDIR)
ARCH := $(shell uname -m)
PLATFORM :=

ifeq ($(ARCH),arm64)
    PLATFORM := --platform=linux/amd64
endif

DOCKER_RUN = docker run ${PLATFORM} --rm -it --net=host -v ${PWD}:/app -w /app gustavofreze/php:8.5-alpine

RESET := \033[0m
GREEN := \033[0;32m
YELLOW := \033[0;33m

.DEFAULT_GOAL := help

.PHONY: configure
configure: ## Configure development environment
	@${DOCKER_RUN} composer configure

.PHONY: configure-and-update
configure-and-update: ## Configure development environment and update dependencies
	@${DOCKER_RUN} composer configure-and-update

.PHONY: tests
tests: ## Run unit and mutation tests with coverage
	@${DOCKER_RUN} composer tests

.PHONY: test-file
test-file: ## Run tests for a specific file (usage: make test-file FILE=ClassNameTest)
	@${DOCKER_RUN} composer test-file ${FILE}

.PHONY: review
review: ## Run lint and static analysis
	@${DOCKER_RUN} composer review

.PHONY: show-reports
show-reports: ## Open coverage and mutation reports in the browser
	@sensible-browser reports/coverage/coverage-html/index.html reports/coverage/mutation-report.html

.PHONY: show-outdated
show-outdated: ## Show outdated direct dependencies
	@${DOCKER_RUN} composer outdated --direct

.PHONY: clean
clean: ## Remove dependencies and generated artifacts
	@sudo chown -R ${USER}:${USER} ${PWD}
	@rm -rf reports vendor .phpunit.cache *.lock

.PHONY: help
help: ## Display this help message
	@echo "Usage: make [target]"
	@echo ""
	@echo "$$(printf '$(GREEN)')Setup$$(printf '$(RESET)')"
	@grep -E '^(configure|configure-and-update):.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*? ## "}; {printf "$(YELLOW)%-25s$(RESET) %s\n", $$1, $$2}'
	@echo ""
	@echo "$$(printf '$(GREEN)')Testing$$(printf '$(RESET)')"
	@grep -E '^(tests|test-file):.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "$(YELLOW)%-25s$(RESET) %s\n", $$1, $$2}'
	@echo ""
	@echo "$$(printf '$(GREEN)')Quality$$(printf '$(RESET)')"
	@grep -E '^(review):.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "$(YELLOW)%-25s$(RESET) %s\n", $$1, $$2}'
	@echo ""
	@echo "$$(printf '$(GREEN)')Reports$$(printf '$(RESET)')"
	@grep -E '^(show-reports|show-outdated):.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "$(YELLOW)%-25s$(RESET) %s\n", $$1, $$2}'
	@echo ""
	@echo "$$(printf '$(GREEN)')Cleanup$$(printf '$(RESET)')"
	@grep -E '^(clean):.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "$(YELLOW)%-25s$(RESET) %s\n", $$1, $$2}'
```
