<?php

namespace TinyBlocks\Currency;

use PHPUnit\Framework\TestCase;

final class CurrencyTest extends TestCase
{
    /**
     * @dataProvider providerForTestValidNameAndValue
     */
    public function testValidNameAndValue(string $name, string $value): void
    {
        self::assertEquals(3, strlen($name));
        self::assertEquals(3, strlen($value));
    }

    /**
     * @dataProvider providerForTestGetDefaultFractionDigits
     */
    public function testGetDefaultFractionDigits(int $expected, Currency $currency): void
    {
        $actual = $currency->getDefaultFractionDigits();

        self::assertEquals($expected, $actual);
    }

    public function providerForTestValidNameAndValue(): array
    {
        return array_map(fn(Currency $currency) => [
            'name'  => $currency->name,
            'value' => $currency->value
        ], Currency::cases());
    }

    public function providerForTestGetDefaultFractionDigits(): array
    {
        return [
            [
                'expected' => 0,
                'currency' => Currency::CLP,
            ],
            [
                'expected' => 2,
                'currency' => Currency::BRL,
            ],
            [
                'expected' => 2,
                'currency' => Currency::USD,
            ],
            [
                'expected' => 2,
                'currency' => Currency::EUR,
            ],
            [
                'expected' => 3,
                'currency' => Currency::KWD,
            ],
            [
                'expected' => 4,
                'currency' => Currency::CLF
            ]
        ];
    }
}
