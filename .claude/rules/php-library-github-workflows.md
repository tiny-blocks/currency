---
description: Structure, ordering, and pinning rules for GitHub Actions workflows in PHP libraries.
paths:
    - ".github/workflows/**/*.yml"
    - ".github/workflows/**/*.yaml"
---

# Workflows

Conventions for GitHub Actions workflows in PHP libraries. CD does not apply. Libraries publish
to Packagist via tags and never deploy.

`.github/workflows/ci.yml` is mandatory and follows the canonical structure defined in the
"ci.yml" section below. Additional workflow files (security scanning, automated triage,
scheduled tasks, dependency updates, etc.) may exist and follow the general rules in this file.
Their trigger, job structure, and steps are chosen by their purpose.

The Composer scripts invoked by `ci.yml` (`composer review`, `composer tests`) are defined in
`php-library-tooling.md`.

## Pre-output checklist

Verify every item before producing or editing any workflow YAML. If any item fails, revise
before outputting.

### Rules for every workflow

These rules apply to `ci.yml` and to every additional workflow in `.github/workflows/`.

1. Keys at the workflow root follow the canonical order `name`, `on`, `concurrency`,
   `permissions`, `jobs`. Keys absent in a given workflow are simply omitted. The relative order
   of the remaining keys is preserved.
2. Properties inside a job follow the canonical order `name`, `needs`, `runs-on`,
   `timeout-minutes`, `outputs`, `env`, `steps`. Same omission rule as above.
3. Inside any block (`env`, `outputs`, `with`, `permissions`), entries are ordered by key length
   ascending.
4. The workflow `name`, every job `name`, and every step `name` are mandatory and use sentence
   case (`Resolve PHP version`, not `RESOLVE_PHP_VERSION` or `resolve_php_version`). Step names
   start with a verb. Job keys describe the job's purpose. Generic keys (`run`, `job`, `do`) are
   discouraged in favor of descriptive identifiers (`auto-assign`, `analyze`, `notify`).
5. `concurrency` is set at the workflow root with `cancel-in-progress: true` and a `group`
   expression scoped by the workflow's trigger:
   - `pull_request`: `<purpose>-${{ github.event.pull_request.number }}`.
   - `issues`, or `issues` combined with `pull_request`:
     `<purpose>-${{ github.event.issue.number || github.event.pull_request.number }}`.
   - `push`, `schedule`, or both: `<purpose>-${{ github.ref }}`.

   `<purpose>` is the workflow's short name (`ci`, `codeql`, `auto-assign`).
6. `permissions` is declared at the workflow root with the minimum scope every job needs.
   Job-level `permissions` blocks are allowed only when a specific job needs a narrower scope
   than the root, never broader.
7. Every job sets `timeout-minutes`. Defaults: 5 for trivial steps (single API call, lightweight
   script), 15 for jobs with PHP setup or test runs, 30 for analysis-heavy jobs (CodeQL,
   security scanning). Adjust based on observed runtime when prior runs exist.
8. Every action is pinned to a fixed major version tag written explicitly. Examples are
   `actions/checkout@v6` and `shivammathur/setup-php@v2`. Never use `@latest`, `@main`, a branch
   name, or a commit SHA. When the existing pin is an explicit minor or patch, derive the major
   version while **preserving the prefix style** of the original tag: `@v2.1.0` → `@v2`,
   `@2.1.0` → `@2`. The action's tag convention is reflected in the existing pin. Web lookup is
   required only when the existing pin is missing, ambiguous, or pointing to a non-version
   reference. Example versions cited in this file may be outdated and are not a license to skip
   the lookup when it is required.
9. Inline shell logic longer than 3 lines is extracted to a script in `scripts/ci/`.
10. All text (workflow name, job names, step names, comments) uses American English with correct
    spelling and punctuation. Sentences and descriptions end with a period.

### Rules specific to ci.yml

These rules apply only to `.github/workflows/ci.yml`. Additional workflows are not bound by them.

1. File path is `.github/workflows/ci.yml`. The workflow `name` field is exactly `CI`.
2. Trigger is `pull_request` only. No `push`, no branch filter, no `workflow_dispatch`.
3. Jobs run in the fixed sequence `resolve-php-version`, `build`, `auto-review`, `tests`. Each
   downstream job lists its upstream jobs in `needs`.
4. PHP version is never hardcoded. The `resolve-php-version` job reads `.require.php` from
   `composer.json` at runtime and exposes the minor version (for example, `8.5`) as the job
   output `php-version`. Downstream jobs reference
   `${{ needs.resolve-php-version.outputs.php-version }}` when setting up PHP.
5. The `auto-review` job runs `composer review`. The `tests` job runs `composer tests`. Both
   scripts are defined in `composer.json` per `php-library-tooling.md`. No other command is
   invoked in either job.
6. The `build` job uploads `vendor/` and `composer.lock` as a single artifact named
   `vendor-artifact`. The `auto-review` and `tests` jobs download that artifact instead of
   running `composer install` again.
7. The `tests` job is the only job that may extend with extra setup required by the library,
   such as service containers, fixture preparation, or environment variables used during
   testing. The other three jobs are identical across every library in the ecosystem.
8. `concurrency.group` is `pr-${{ github.event.pull_request.number }}`. `timeout-minutes` is 5
   for `resolve-php-version` and 15 for `build`, `auto-review`, and `tests`. `permissions` is
   `contents: read`.

## ci.yml

`ci.yml` is the mandatory workflow that gates every pull request. It contains four jobs in the
exact order below. The first three jobs are identical across every library. Only `tests` may
extend with extra setup required by the library.

### Resolve PHP version

Reads `.require.php` from `composer.json` and exposes the minor version (for example, `8.5`) as the
output `php-version`. A single step uses `jq` and a short regex to extract the value. Downstream jobs
consume the output to configure their PHP setup.

### Build

Sets up PHP using the resolved version, validates `composer.json`, installs dependencies with
`--no-progress --optimize-autoloader --prefer-dist --no-interaction`, and uploads `vendor/` and
`composer.lock` as the artifact `vendor-artifact`.

### Auto review

Depends on `resolve-php-version` and `build`. Downloads `vendor-artifact`, sets up PHP, and runs
`composer review`. The `review` script in `composer.json` aggregates lint, static analysis, and style
checks for the library.

### Tests

Depends on `resolve-php-version` and `auto-review`. Downloads `vendor-artifact`, sets up PHP, and runs
`composer tests`. Any setup required by the library's tests (service containers, fixture preparation,
environment variables used during testing) lives in this job only.

## Reference shape

The YAML below is the canonical minimal form. Every library starts from this exact shape and extends
only the `tests` job when its tests require extra setup. Action versions cited here may be outdated.
Look up the current major version of every action via web search before adopting this shape verbatim.

### Minimal workflow

```yaml
name: CI

on:
  pull_request:

concurrency:
  group: pr-${{ github.event.pull_request.number }}
  cancel-in-progress: true

permissions:
  contents: read

jobs:
  resolve-php-version:
    name: Resolve PHP version
    runs-on: ubuntu-latest
    timeout-minutes: 5
    outputs:
      php-version: ${{ steps.config.outputs.php-version }}
    steps:
      - name: Checkout
        uses: actions/checkout@v6

      - name: Resolve PHP version from composer.json
        id: config
        run: |
          version=$(jq -r '.require.php' composer.json | grep -oP '\d+\.\d+' | head -1)
          echo "php-version=$version" >> "$GITHUB_OUTPUT"

  build:
    name: Build
    needs: resolve-php-version
    runs-on: ubuntu-latest
    timeout-minutes: 15
    steps:
      - name: Checkout
        uses: actions/checkout@v6

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          tools: composer:2
          php-version: ${{ needs.resolve-php-version.outputs.php-version }}

      - name: Validate composer.json
        run: composer validate --no-interaction

      - name: Install dependencies
        run: composer install --no-progress --optimize-autoloader --prefer-dist --no-interaction

      - name: Upload vendor and composer.lock as artifact
        uses: actions/upload-artifact@v7
        with:
          name: vendor-artifact
          path: |
            vendor
            composer.lock

  auto-review:
    name: Auto review
    needs: [resolve-php-version, build]
    runs-on: ubuntu-latest
    timeout-minutes: 15
    steps:
      - name: Checkout
        uses: actions/checkout@v6

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          tools: composer:2
          php-version: ${{ needs.resolve-php-version.outputs.php-version }}

      - name: Download vendor artifact from build
        uses: actions/download-artifact@v8
        with:
          name: vendor-artifact
          path: .

      - name: Run review
        run: composer review

  tests:
    name: Tests
    needs: [resolve-php-version, auto-review]
    runs-on: ubuntu-latest
    timeout-minutes: 15
    steps:
      - name: Checkout
        uses: actions/checkout@v6

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          tools: composer:2
          php-version: ${{ needs.resolve-php-version.outputs.php-version }}

      - name: Download vendor artifact from build
        uses: actions/download-artifact@v8
        with:
          name: vendor-artifact
          path: .

      - name: Run tests
        run: composer tests
```

### Extending the tests job

When the library's tests need external services, env vars, or fixture preparation, the additions live
inside the `tests` job only. The example below shows the same `tests` job extended with a MySQL service
container and the env vars consumed by the test suite.

```yaml
tests:
  name: Tests
  needs: [resolve-php-version, auto-review]
  runs-on: ubuntu-latest
  timeout-minutes: 15
  env:
    DB_HOST: 127.0.0.1
    DB_NAME: library_test
    DB_PORT: '3306'
    DB_USER: library
    DB_PASSWORD: library
  services:
    mysql:
      image: mysql:8
      ports:
        - 3306:3306
      env:
        MYSQL_DATABASE: library_test
        MYSQL_ROOT_PASSWORD: library
      options: >-
        --health-cmd="mysqladmin ping"
        --health-interval=10s
        --health-timeout=5s
        --health-retries=5
  steps:
    - name: Checkout
      uses: actions/checkout@v6

    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        tools: composer:2
        php-version: ${{ needs.resolve-php-version.outputs.php-version }}

    - name: Download vendor artifact from build
      uses: actions/download-artifact@v8
      with:
        name: vendor-artifact
        path: .

    - name: Run tests
      run: composer tests
```
