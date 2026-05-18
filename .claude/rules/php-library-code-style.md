---
description: Semantic code rules for all PHP files in libraries.
paths:
    - "src/**/*.php"
    - "tests/**/*.php"
---

# Code style

Semantic rules for all PHP files in libraries. Formatting rules covered by `PSR-12` are enforced
by `phpcs.xml`. Two formatting rules outside `PSR-12` (no vertical alignment, no trailing comma in
multi-line lists) are documented at the end of this file under "Formatting overrides". Complexity
rules live in `php-library-modeling.md`. Folder structure, public API boundary, and the semantics
of `Internal/` live in `php-library-architecture.md`.

## Pre-output checklist

Verify every item before producing any PHP code. If any item fails, revise before outputting.

1. `declare(strict_types=1)` is present.
2. All parameters, return types, and properties have explicit types.
3. Constructor property promotion is used.
4. Named arguments are used at call sites for own code, tests, and third-party library methods
   (for example, tiny-blocks). Never use named arguments on:
    - Native PHP functions (`array_map`, `in_array`, `preg_match`, `is_null`,
      `iterator_to_array`, `sprintf`, `implode`, and similar).
    - Native PHP enum methods (`from`, `tryFrom`, `cases`).
    - PHPUnit assertions and expectations (`assertEquals`, `assertSame`, `assertTrue`,
      `expectException`, and similar).
    - Interfaces from PHP-FIG PSR standards (PSR-7 `withHeader`, PSR-18 `sendRequest`, etc.).
      The PSR contract does not include parameter names. Implementations may rename parameters.
    - Calls that include variadic spread (`...$args`). PHP rejects positional argument unpacking
      after named arguments. When the caller passes through a `...$variadic`, all arguments are
      positional. New own-code APIs should prefer a typed collection parameter over a variadic
      so named-argument call sites remain possible.

   Native PHP **class constructors** (`parent::__construct` calls to `\Exception`,
   `\RuntimeException`, `\InvalidArgumentException`, `\LogicException`, and similar) are not
   in the list above. They accept named arguments, and rule 8 requires using them whenever
   the positional call would pass an argument whose value equals the parameter's default.
   Example: `parent::__construct(message: sprintf(...), previous: $previous)` instead of
   `parent::__construct(sprintf(...), 0, $previous)`. The exclusion above covers native
   functions and enum methods, not native class instantiation.
5. Classes follow the rules in "Inheritance and constructors". `final readonly` is the default,
   with documented exceptions for extension points and for parents that are not `readonly`.
6. Members are ordered constants first, then constructor, then static methods, then instance
   methods. Within each group, order by body size ascending (number of lines between `{` and `}`).
   Constants and enum cases, which have no body, are ordered by name length ascending. This
   ordering may be overridden only when the alternative carries explicit documentation value:
   grouping by domain class with section markers (HTTP status codes by 1xx/2xx/3xx/etc),
   mirroring the order of an implemented interface, or similar evident structure. The override
   must be obvious at first reading.

   **At call sites** (chained method calls in production code, tests, or documentation
   examples), consecutive method invocations on the same receiver are ordered by the **visible
   width** of each call expression ascending. The body is not visible at the call site, so the
   visible width is the practical proxy for body size. Boolean toggles such as `->secure()` and
   `->httpOnly()` come before parameterized `with*` builders for the same reason. When two
   calls have equal width, order them alphabetically by method name.

   **Terminal methods that change the receiver type** stay at the end of the chain regardless
   of width. A `build()` that returns the built value, a `commit()` that finalizes a unit of
   work, a `send()` that flushes a request, are terminal: the chain ends with them. The
   ordering rule applies only to consecutive calls on the same receiver type; calls that
   transition to a different type are not reorderable. The same applies in reverse to the
   factory or accessor that starts the chain (`Cookie::create(...)`, `$repository`) — it stays
   at its position.
7. Constructor parameters are ordered by parameter name length ascending (count the name only,
   without `$` or type), except when parameters have an implicit semantic order (for example,
   `$start/$end`, `$from/$to`, `$startAt/$endAt`), which takes precedence. Parameters with default
   values go last, regardless of name length. The same rule applies to named arguments at call
   sites. Example order: `$id` (2), `$value` (5), `$status` (6), `$precision` (9).
8. Never pass an argument whose value equals the parameter's default. Omit the argument entirely.
   Example with `toArray(KeyPreservation $keyPreservation = KeyPreservation::PRESERVE)`. The call
   `$collection->toArray(keyPreservation: KeyPreservation::PRESERVE)` becomes
   `$collection->toArray()`. Only pass the argument when the value differs from the default.
9. No `else` or `else if` exists anywhere. Use early returns, polymorphism, or map dispatch instead.
10. No abbreviations appear in identifiers. Use `$index` instead of `$i`, `$account` instead of
    `$acc`.
11. No generic identifiers exist. Use domain-specific names instead. Examples are `$data` to
    `$payload`, `$value` to `$totalAmount`, `$item` to `$element`, `$info` to `$currencyDetails`,
    `$result` to `$conversionOutcome`.
12. No raw arrays exist where a typed collection or value object is available. When data is
    `Collectible`, use the `tiny-blocks/collection` fluent API (`Collection`, `Collectible`). Use
    `createLazyFrom` when elements are consumed once. Raw arrays are acceptable only for primitive
    configuration data, variadic pass-through, and interop at system boundaries. See "Collection
    usage" for the full rule and example.
13. No private methods exist except for private constructors in factory patterns, methods inside
    `src/Internal/` (implementation detail by definition, where the namespace is the abstraction
    boundary), and `setUp` or `tearDown` overrides in PHPUnit test classes. Outside these cases,
    inline trivial logic at the call site or extract it to a collaborator or value object.
14. No logic is duplicated across two or more places (DRY).
15. No abstraction exists without real duplication or isolation need (KISS).
16. No inline comments exist in `src/` or `tests/`, except `# TODO: <reason>` when implementation
    is unknown, uncertain, or intentionally deferred. Code is the documentation. Block comments
    (`/* */`) never appear outside docblocks (`/** */`). The `#` style for inline PHP comments
    applies only to code examples inside Markdown files (see `php-library-documentation.md`).
17. No dead or unused code exists. Remove unreferenced classes, methods, constants, and imports.
18. Never create public methods, constants, or classes in `src/` solely to serve tests. If
    production code does not need it, it does not exist.
19. Format strings with placeholders (`%s`, `%d`, `%f`, etc.) are assigned to a `$template`
    variable before being passed to `sprintf`. The variable assignment and the `sprintf` call live
    on separate statements. See "Format strings" for examples.
20. All class references use `use` imports at the top of the file. Fully qualified names inline are
    prohibited.
21. Return types and `new` calls use the explicit class name. `self` is prohibited as a type,
    as a return type, and in `new self()` instantiation. Constant access via `self::CONST_NAME`
    is permitted. `static` is permitted only inside extension-point classes (declared `class`
    without `final readonly`) and inside traits, where late static binding lets subclasses or
    consuming classes instantiate the correct concrete type. In every other context, use the
    class name.
22. Always use the most current and clean syntax available in the target PHP version. Prefer
    `match` over `switch`, first-class callables over `Closure::fromCallable()`, readonly promotion
    over manual assignment, enum methods over external switch or if chains, named arguments over
    positional ambiguity (except where excluded by rule 4), `Collection::map` over foreach
    accumulation, and **unparenthesized constructor chaining** (PHP 8.4+):
    `new Foo()->bar()` instead of `(new Foo())->bar()`. The parentheses around the `new`
    expression are no longer required and add visual noise.
23. All identifiers, comments, and documentation use American English. See "American English" for
    the spelling list.

## Naming

- Internal code (variables, methods, classes) uses `camelCase`.
- Constants and enum-backed values when representing codes use `SCREAMING_SNAKE_CASE`.
- Names describe what in domain terms, not how technically. `$monthlyRevenue` instead of
  `$calculatedValue`. Generic technical verbs are avoided. See `php-library-modeling.md` for the
  full banlist of generic and anemic names.
- Booleans use predicate form. Examples are `isActive`, `hasPermission`, `wasProcessed`.
- Collections are always plural. Examples are `$orders`, `$lines`.
- Methods returning `bool` use prefixes `is`, `has`, `can`, `was`, `should`.

## Class self-references

Type declarations, return types, and `new` calls inside a class use the explicit class name.
The class name is unambiguous, survives refactors that move the method to a different class,
and reads identically inside the class body and at the call site.

- `self` is prohibited everywhere as a type, as a return type, and in `new self()`
  instantiation. Constant access via `self::CONST_NAME` is **permitted**. The prohibition
  covers the forms that carry refactoring ambiguity when a method moves to a different class
  (the type-or-instantiation forms). Constant access does not have that ambiguity because the
  constant is declared in the same class body.
- `static` is permitted only inside extension-point classes (declared `class` without
  `final readonly`) and inside traits, where late static binding is required for subclasses or
  consuming classes to instantiate the correct concrete type.
- In every other context (the default `final readonly class`, factory methods, return types),
  use the class name.

**Prohibited.** `self` as return type and `new self()` inside a final class:

```php
final readonly class UserAgent
{
    public static function from(string $product): self
    {
        return new self(product: $product);
    }
}
```

**Correct.** Explicit class name in a final class:

```php
final readonly class UserAgent
{
    public static function from(string $product): UserAgent
    {
        return new UserAgent(product: $product);
    }
}
```

**Correct.** `static` permitted in an extension-point class:

```php
class Collection
{
    public static function createFrom(iterable $elements): static
    {
        return new static(elements: $elements);
    }
}
```

## Inheritance and constructors

- All classes are `final readonly` by default.
- Use `class` (without `final` or `readonly`) only when the class is designed as an extension point
  for consumers, for example `Collection` or `ValueObject`.
- Use `final class` without `readonly` only when the parent class is not readonly, for example
  when extending a third-party abstract class.
- Use `final class` without `readonly` is also permitted for `src/Internal/` collaborators that
  carry intrinsically mutable state (resource handles, counters, cursors) where the mutation is
  central to the class's responsibility (`Stream` closing a resource, `Cursor` advancing a
  position). The class must remain confined to `src/Internal/`.
- Use `final class` without `readonly` for classes that consist exclusively of `static` methods
  (no instance properties, no instance methods, only static factories or utilities). Pair it
  with `private function __construct() {}` to prevent instantiation. `readonly` is meaningless
  without instance state, and the private constructor signals that the class is a static
  surface, not a value type.
- Inheritance between concrete classes is prohibited. Every concrete class is `final`.
- Polymorphism uses interfaces plus composition, never extension of concrete types.
- The only allowed `extends` is against framework or SPL base classes that the language requires.
  Examples are `RuntimeException`, `LogicException`, `PHPUnit\Framework\TestCase`.
- Constructors of `final` classes are `private` when paired with named factory methods, `public`
  otherwise. `protected` constructors are prohibited because no subclasses exist to call them.

## Comparisons

1. Null checks use `is_null($variable)`, never `$variable === null`.
2. Empty string checks on typed `string` parameters use `$variable === ''`. Avoid `empty()` on
   typed strings because `empty('0')` returns `true`.
3. Mixed or untyped checks (value may be `null`, empty string, `0`, or `false`) use
   `empty($variable)`.

## American English

All identifiers, enum values, comments, and error codes use American English spelling. Examples
are `canceled` (not `cancelled`), `organization` (not `organisation`), `initialize` (not
`initialise`), `behavior` (not `behaviour`), `modeling` (not `modelling`), `labeled` (not
`labelled`), `fulfill` (not `fulfil`), `color` (not `colour`).

## PHPDoc

### When required

- Every method of an interface, **including interfaces declared inside `src/Internal/`**.
  Interfaces define contracts. The contract is documentation by definition, regardless of
  namespace. The `Internal/` boundary applies to implementations, not to the contracts that
  internal collaborators expose to each other.
- Every public method of a concrete class outside `src/Internal/`. Public classes are at the
  public API boundary by definition. Consumers call every public method directly, and the
  PHPDoc is the contract for each call. Trivial getters and `with*` methods are not exempt.
  The only exception is a public method whose contract is already documented on an implemented
  interface (the interface carries the docblock).

### When prohibited

- Constructors. The constructor signature with property promotion is self-documenting. Parameter
  types are already explicit in the signature.
- Private and protected methods.
- Public methods of concrete classes whose contract is already documented on an implemented
  interface. The interface carries the docblock.
- Anything inside `src/Internal/`. Internal types are implementation detail and must not carry
  PHPDoc. The namespace itself is the boundary. See `php-library-architecture.md` for the
  architectural meaning of `Internal/`. **Exception**: interfaces and their methods. An
  interface declared inside `src/Internal/` still defines a contract, and the contract is
  documented per `### When required` regardless of namespace. The prohibition covers concrete
  classes, traits, enums, and anonymous classes inside `Internal/`, never interfaces.
- Anywhere inside `tests/`. Test methods name the scenario via the `testXxxWhenYyyGivenThenZzz`
  naming convention, and the `@Given`/`@When`/`@Then`/`@And` annotation blocks defined in
  `php-library-testing.md` describe the steps. PHPDoc documentation (summary plus
  `@param`/`@return` descriptions) is prohibited on test methods, data providers, fixtures,
  setUp/tearDown overrides, and anonymous classes inside tests. The BDD annotations are not
  PHPDoc documentation in the sense of this section and remain required per the testing rule.
- Single-line PHPDocs with only a tag (`/** @param ... */`, `/** @return ... */`,
  `/** @throws ... */`). PHPDoc always opens with a summary line. Bare-tag docblocks are
  prohibited regardless of how few tags they carry.

The prohibitions above apply to **every form of PHPDoc** in the prohibited scope:
method-level docblocks, property-level docblocks, inline `@var` annotations on local variables,
and PHPDoc blocks placed above anonymous functions or closures inside method bodies. Inside
`src/Internal/` and `tests/`, zero PHPDoc is the rule with no exception. PHPStan errors that
result from the missing annotations route through `ignoreErrors` (see below).

The PHPDoc prohibitions above take priority over the typed-array case. When PHPStan at
`level: max` flags a missing iterable value type (`missingType.iterableValue`,
`argument.type`, `return.type`):

- On a **constructor parameter** → suppress via `ignoreErrors` in `phpstan.neon.dist`. Do not
  add PHPDoc.
- On anything inside **`src/Internal/`** (concrete classes, traits, enums) → suppress via
  `ignoreErrors`. Do not add PHPDoc. Interfaces inside `src/Internal/` are the exception:
  they carry PHPDoc per `### When required`, and the PHPStan errors they raise are resolved
  through the PHPDoc, never through `ignoreErrors`.
- On anything inside **`tests/`** → suppress via `ignoreErrors`. Do not add PHPDoc.
- On a **public method of a public (non-Internal) class** → add full PHPDoc with summary,
  `@param` descriptions, and the typed-array information. The bare-tag form remains
  prohibited. This is the normal case where PHPDoc is permitted by "When required" above.

The summary requirement and the bare-tag prohibition are never waived. Use `ignoreErrors` only
when the context (constructor, `src/Internal/`, `tests/`) makes PHPDoc impossible. Every public
method of a public concrete class carries PHPDoc per "When required", whether the method
has typed-array parameters.

### Style

- Summary on the first line, in domain terms. **Mandatory.** PHPDoc without a summary line is
  prohibited, even when it carries a single `@param` or `@return`.
- Optional detailed body in `<p>` paragraphs below the summary.
- Tags use the form `@param Type $name Description.`, `@return Type Description.`,
  `@throws ExceptionClass If <condition>.`.
- Document `@throws` for every exception the method may raise.
- HTML tags allowed inside descriptions are `<p>` for paragraphs, `<ul><li>` for lists,
  `<code>` for inline code, `<em>` and `<strong>` for emphasis.

### Summary patterns

The summary line is not a creative intent statement. It is a template selected by the method's
name prefix. Apply the matching template. Only methods with no matching prefix require a
free-form one-line summary in domain terms.

| Method shape                                                            | Template                                                                       |
|-------------------------------------------------------------------------|--------------------------------------------------------------------------------|
| Static factory (`create`, `from`, `fromX`, `with*` when static)         | `Creates a {ClassName} from {input}.` or `Builds a {ClassName} with {fields}.` |
| `with*` instance method                                                 | `Returns a copy of the {ClassName} with the {field} replaced.`                 |
| Getter (no prefix, returns a property: `code()`, `body()`, `headers()`) | `Returns the {field}.`                                                         |
| Predicate (`is*`, `has*`, `can*`, `was*`, `should*`)                    | `Tells whether {condition}.`                                                   |
| Converter (`toArray`, `toString`, `asX`)                                | `Returns the {ClassName} as {target shape}.`                                   |
| `apply*`, `merge*`, `add*`, and other side-effect-free operations       | One-line summary in domain terms describing the operation.                     |

The patterns are mandatory when applicable. They make summary lines mechanical: substitute
`{ClassName}` and `{field}` and the summary is complete. No per-method intent decision is
required. Volume is never a reason to skip the summary. Many methods just mean applying the
template many times.

### Cross-references

- `{@see ClassName}` for links to other types in the codebase.
- `@see Author, <em>Title</em> (Publisher, Year), Chapter X.` for bibliographical references.

### Examples

**Prohibited.** Single-line bare-tag PHPDoc, no summary:

```php
/** @param array<string, mixed>|null $body */
public static function with(Code $code, ?array $body = null): Response
```

**Prohibited.** PHPDoc on a constructor:

```php
/** @param array<string, mixed> $entries */
public function __construct(public array $entries)
{
}
```

**Prohibited.** PHPDoc on a **concrete class** inside `src/Internal/` (the prohibition does
not extend to interfaces; see "Correct" below for an Internal/ interface):

```php
namespace TinyBlocks\Http\Internal\Client;

final readonly class Url
{
    /** @param array<string, scalar>|null $query */
    public static function compose(string $path, ?array $query, string $baseUrl): string
    {
    }
}
```

**Correct.** Interface declared **inside `src/Internal/`** still carries PHPDoc on every
method. The Internal/ prohibition covers concrete classes; interfaces are exempt because they
are the contract:

```php
namespace TinyBlocks\Http\Internal\Client;

interface RequestResolver
{
    /**
     * Resolves the given URL against the configured base URL.
     *
     * @param string $url The path or absolute URL to resolve.
     * @return string The absolute URL to dispatch.
     * @throws MalformedPath If the URL violates RFC 3986.
     */
    public function resolve(string $url): string;
}
```

**Correct.** Generic array type with summary and `@param` description:

```php
/**
 * Builds a synthesized response from a status code and an optional body.
 *
 * @param array<string, mixed>|null $body The response body as an associative array.
 * @return Response The synthesized response instance.
 */
public static function with(Code $code, ?array $body = null): Response
```

**Correct.** Interface with rich description, paragraphs, cross-references, and bibliography:

```php
/**
 * Money tied to a specific currency.
 *
 * <p>Operations between different currencies raise <code>CurrencyMismatch</code>. Arithmetic
 * preserves the currency.</p>
 *
 * <p>Sibling of {@see Quantity}, not a parent. <code>Money</code> carries currency semantics.</p>
 *
 * @see Eric Evans, <em>Domain-Driven Design</em> (Addison-Wesley, 2003), Chapter 5.
 */
interface Money
{
    /**
     * Adds the given amount.
     *
     * @param Money $other The amount to add.
     * @return Money A new instance with the summed amount.
     * @throws CurrencyMismatch If <code>$other</code> has a different currency.
     */
    public function add(Money $other): Money;
}
```

**Correct.** Concrete class with a short summary and direct tags:

```php
/**
 * IANA timezone identifier (e.g. America/Sao_Paulo).
 */
final readonly class Timezone
{
    /**
     * Creates a Timezone from a valid IANA identifier.
     *
     * @param string $identifier The IANA timezone identifier.
     * @return Timezone The created instance.
     * @throws InvalidTimezone If the identifier is not a valid IANA timezone.
     */
    public static function from(string $identifier): Timezone
    {
        # ...
    }
}
```

## Dependencies

When the library needs an external dependency, prefer packages from the `tiny-blocks` ecosystem
(https://github.com/tiny-blocks) whenever a suitable option exists. Reach for outside packages
only when the ecosystem has no equivalent that fits the use case.

## Collection usage

When a property or parameter is `Collectible`, use its fluent API. Never break out to raw array
functions such as `array_map`, `array_filter`, `iterator_to_array`, or `foreach` plus accumulation.
The same applies to `filter()`, `reduce()`, `each()`, and every other `Collectible` operation.
Chain them fluently. Never materialize with `iterator_to_array` to then pass into a raw `array_*`
function.

**Prohibited.** `array_map` plus `iterator_to_array` on a `Collectible`:

```php
$names = array_map(
    static fn(Element $element): string => $element->name(),
    iterator_to_array($collection)
);
```

**Correct.** Fluent chain with `map()` plus `toArray()`:

```php
$names = $collection
    ->map(transformations: static fn(Element $element): string => $element->name())
    ->toArray(keyPreservation: KeyPreservation::DISCARD);
```

## Format strings

When building a message with placeholders, assign the format string to a `$template` variable
first. Pass it to `sprintf` on a separate statement. The format and the data are visually
separated, and the template line stays scannable.

**Prohibited.** Format string inline with the call:

```php
if ($value < 0 || $value > 16) {
    throw new PrecisionOutOfRange(
        message: sprintf('Precision must be between 0 and 16, got %d.', $value)
    );
}
```

**Correct.** Format string in a `$template` variable:

```php
if ($value < 0 || $value > 16) {
    $template = 'Precision must be between 0 and 16, got %d.';

    throw new PrecisionOutOfRange(message: sprintf($template, $value));
}
```

## Constructor chaining

PHP 8.4 allows chained method calls directly on a `new` expression without wrapping it in
parentheses. The parentheses are no longer required and only add visual noise. Apply this
everywhere a `new` is followed by a method call.

**Prohibited.** Parentheses around the `new` expression:

```php
$body = (new ServerRequest(method: 'GET', uri: 'https://api.example.com'))
    ->withHeader('Accept', 'application/json')
    ->getBody();
```

**Correct.** No parentheses:

```php
$body = new ServerRequest(method: 'GET', uri: 'https://api.example.com')
    ->withHeader('Accept', 'application/json')
    ->getBody();
```

## Formatting overrides

Three formatting rules are not covered by the canonical `phpcs.xml` (which references `PSR-12`
only). Apply them manually.

### No vertical alignment in parameter lists

Use a single space between the type and the variable name in parameter lists (constructors,
function signatures, closures). Never pad with extra spaces to align columns. This rule applies
only to parameter lists, not to other contexts that use `=>` alignment (see "Vertical alignment
of `=>`" below).

**Prohibited.** Vertical alignment of types:

```php
public function __construct(
    public OrderId     $id,
    public Money       $total,
    public Customer    $customer,
    public Precision   $precision
) {}
```

**Correct.** Single space between type and variable:

```php
public function __construct(
    public OrderId $id,
    public Money $total,
    public Customer $customer,
    public Precision $precision
) {}
```

### Vertical alignment of `=>` in match arms and array literals

Multi-line `match` expressions and multi-line array literals with `=>` align the `=>` column
across all arms or entries by padding shorter left-hand sides with spaces. Single-line cases
(one-arm match, single-line array) keep the standard PSR-12 single-space form.

**Prohibited.** Unaligned `=>` in match:

```php
return match ($this) {
    self::MAX_AGE => sprintf($template, $this->value, $value),
    default => $this->value
};
```

**Correct.** Aligned `=>` in match:

```php
return match ($this) {
    self::MAX_AGE => sprintf($template, $this->value, $value),
    default       => $this->value
};
```

**Prohibited.** Unaligned `=>` in array literal:

```php
return [
    'name' => 'Gustavo',
    'role' => 'developer',
    'company' => 'Anthropic'
];
```

**Correct.** Aligned `=>` in array literal:

```php
return [
    'name'    => 'Gustavo',
    'role'    => 'developer',
    'company' => 'Anthropic'
];
```

### No trailing comma in multi-line lists

Never place a trailing comma after the last element of any multi-line list. Applies to parameter
lists, argument lists, array literals, match arms, and every other comma-separated multi-line
structure. PHP accepts trailing commas in these positions, but this ecosystem prohibits them for
visual consistency.

**Prohibited.** Trailing comma after the last argument:

```php
new Precision(
    value: 2,
    rounding: RoundingMode::HALF_UP,
);
```

**Correct.** No trailing comma:

```php
new Precision(
    value: 2,
    rounding: RoundingMode::HALF_UP
);
```
