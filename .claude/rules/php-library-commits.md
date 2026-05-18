---
description: Conventional Commits format. Applied on request when generating commit messages.
---

# Commits

Applied only when generating commit messages, never automatically. All commit messages are
written in English.

## Format

`<type>: <Description>`

The description starts with a capital letter, uses imperative present tense ("Add", "Fix",
"Change", not "Added", "Adds", or "Adding"), and ends with a period. Subject under 300
characters. If it does not fit, split the change into multiple commits or move detail into the
body.

Scopes are prohibited. `feat(orders): ...` is wrong. The type stands alone.

## Allowed types

Each entry below is a bullet that starts with a capital letter and ends with a period. This is
the canonical example of bullet punctuation enforced everywhere in this document.

- `ci` for CI configuration changes.
- `fix` for a bug fix.
- `feat` for a user-facing feature.
- `docs` for documentation only.
- `test` for adding or correcting tests.
- `chore` for maintenance with no production code change.
- `build` for build or dependency changes.
- `revert` for reverting a previous commit.
- `refactor` for a code change that neither fixes a bug nor adds a feature.

`style` is not used. Formatting is enforced by the linter and never appears as a standalone
commit.

## Subject examples

Good:

- `fix: Handle zero-amount transactions.`
- `feat: Add order cancellation endpoint.`
- `refactor: Extract OrderStatus into its own enum.`

Bad:

- `Added order cancellation` is past tense, missing type, missing period.
- `feat: Adds order cancellation.` is third-person singular instead of imperative.
- `feat: added order cancellation.` starts lowercase and is past tense.
- `feat: Add cancellation, and fix billing rounding.` bundles two changes. Split.
- `feat(orders): Add cancellation.` uses a scope. Prohibited.

## Body

The body is **optional and rarely needed**. Single-purpose commits never have a body. Add a body
ONLY when the reason cannot be inferred from the diff (a non-obvious trade-off, a workaround for
an external bug, a decision worth recording).

Separate the body from the subject with a blank line. Wrap at 72 characters per line. Explain
why, not what. The diff already shows what.

## Prose vs. bullets in the body

**Default to prose.** One or two paragraphs fits almost every commit that has a body at all.

**Use bullets only when ALL of these are true:**

1. The commit covers 3 or more independent changes that genuinely belong in the same commit.
2. The list cannot be expressed as continuous prose without becoming disconnected sentences.
3. Each item is independently meaningful (no sub-bullets, no continuation across bullets).

A two-item bullet list is the wrong shape. Use prose.

## Bullet formatting (when used)

Every bullet starts with a capital letter and ends with a period. Imperative verb in present
tense, same as the subject line. Without exception.

Wrong (do NOT generate):

- `add the OrderCancelling port` lowercase, missing period.
- `Add the OrderCancelling port` capital but missing period.
- `Adds the OrderCancelling port.` third-person singular instead of imperative.

## Body example with bullets

```
feat: Add order cancellation flow.

- Add the OrderCancelling inbound port and OrderCancellingHandler.
- Add the CancelOrder command and its validator.
- Cover the cancellation path in the integration test suite.
```

## Body example with prose (preferred for most commits)

```
fix: Handle zero-amount transactions.

The payment gateway rejects zero-amount charges with a generic 400 instead
of a documented error code, so the adapter short-circuits before the HTTP
call and raises ZeroAmountNotAllowed directly.
```

## Commit splitting

Prefer one logical change per commit. Refactor commits never modify behavior. When a task
requires multiple types of change, produce multiple commits in order: `refactor` first, then
`feat` or `fix` on top.
