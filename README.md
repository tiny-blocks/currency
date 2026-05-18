# Currency

[![License](https://img.shields.io/badge/license-MIT-green)](https://github.com/tiny-blocks/currency/blob/main/LICENSE)

* [Overview](#overview)
* [Installation](#installation)
* [How to use](#how-to-use)
* [License](#license)
* [Contributing](#contributing)

## Overview

Models [ISO-4217](https://www.iso.org/iso-4217-currency-codes.html) currencies as a PHP enum, covering all standard
currency codes with their correct number of fraction digits. Resolves edge cases such as zero-decimal currencies (JPY,
KRW), three-decimal currencies (BHD, KWD), and four-decimal currencies (CLF, UYW). Backed by a native PHP enum for
zero-overhead comparison and type safety.

## Installation

```bash
composer require tiny-blocks/currency
```

## How to use

The library exposes the `Currency` enum. In addition to the alphabetic code, the matching number of fraction digits is
available for the respective currency.

```php
<?php

declare(strict_types=1);

use TinyBlocks\Currency\Currency;

$currency = Currency::USD;

$currency->name;                # USD
$currency->value;               # USD
$currency->getFractionDigits(); # 2
```

## License

Currency is licensed under [MIT](LICENSE).

## Contributing

Please follow the [contributing guidelines](https://github.com/tiny-blocks/tiny-blocks/blob/main/CONTRIBUTING.md) to
contribute to the project.
