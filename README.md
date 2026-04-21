# Currency

[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

* [Overview](#overview)
* [Installation](#installation)
* [How to use](#how-to-use)
* [License](#license)
* [Contributing](#contributing)

<div id='overview'></div> 

## Overview

Models [ISO-4217](https://www.iso.org/iso-4217-currency-codes.html) currencies as a PHP enum, covering all standard
currency codes with their correct number of fraction digits. Resolves edge cases such as zero-decimal currencies (JPY,
KRW), three-decimal currencies (BHD, KWD), and four-decimal currencies (CLF, UYW). Backed by a native PHP enum for
zero-overhead comparison and type safety.

<div id='installation'></div>

## Installation

```bash
composer require tiny-blocks/currency
```

<div id='how-to-use'></div>

## How to use

The library exposes a concrete implementation through the `Currency` enum. Besides, the alphabetic code, you can
get the default amount fraction digits for the respective currency.

```php
$currency = Currency::USD;

$currency->name;                # USD
$currency->value;               # USD
$currency->getFractionDigits(); # 2
```

<div id='license'></div>

## License

Currency is licensed under [MIT](LICENSE).

<div id='contributing'></div>

## Contributing

Please follow the [contributing guidelines](https://github.com/tiny-blocks/tiny-blocks/blob/main/CONTRIBUTING.md) to
contribute to the project.
