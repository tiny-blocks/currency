# Currency

[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

* [Overview](#overview)
* [Installation](#installation)
* [How to use](#how-to-use)
* [License](#license)
* [Contributing](#contributing)

<div id='overview'></div> 

## Overview

Value Object representing a currency using [ISO-4217]( https://www.iso.org/iso-4217-currency-codes.html) specifications.

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

echo $currency->name;                       # USD
echo $currency->value;                      # USD
echo $currency->getDefaultFractionDigits(); # 2
```

## License

Math is licensed under [MIT](/LICENSE).

<div id='contributing'></div>

## Contributing

Please follow the [contributing guidelines](https://github.com/tiny-blocks/tiny-blocks/blob/main/CONTRIBUTING.md) to
contribute to the project.
