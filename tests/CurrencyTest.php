<?php

declare(strict_types=1);

namespace TinyBlocks\Currency;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CurrencyTest extends TestCase
{
    #[DataProvider('currenciesDataProvider')]
    public function testValidNameAndValue(string $name, string $value): void
    {
        self::assertSame(3, strlen($name));
        self::assertSame(3, strlen($value));
    }

    #[DataProvider('fractionDigitsDataProvider')]
    public function testGetFractionDigits(int $expected, Currency $currency): void
    {
        $actual = $currency->getFractionDigits();

        self::assertSame($expected, $actual);
    }

    public static function currenciesDataProvider(): array
    {
        return array_map(static fn(Currency $currency): array => [
            'name'  => $currency->name,
            'value' => $currency->value
        ], Currency::cases());
    }

    public static function fractionDigitsDataProvider(): array
    {
        return [
            'Currency BIF with digits 0' => ['currency' => Currency::BIF, 'expected' => 0],
            'Currency CLP with digits 0' => ['currency' => Currency::CLP, 'expected' => 0],
            'Currency DJF with digits 0' => ['currency' => Currency::DJF, 'expected' => 0],
            'Currency GNF with digits 0' => ['currency' => Currency::GNF, 'expected' => 0],
            'Currency ISK with digits 0' => ['currency' => Currency::ISK, 'expected' => 0],
            'Currency JPY with digits 0' => ['currency' => Currency::JPY, 'expected' => 0],
            'Currency KMF with digits 0' => ['currency' => Currency::KMF, 'expected' => 0],
            'Currency KRW with digits 0' => ['currency' => Currency::KRW, 'expected' => 0],
            'Currency PYG with digits 0' => ['currency' => Currency::PYG, 'expected' => 0],
            'Currency RWF with digits 0' => ['currency' => Currency::RWF, 'expected' => 0],
            'Currency UGX with digits 0' => ['currency' => Currency::UGX, 'expected' => 0],
            'Currency UYI with digits 0' => ['currency' => Currency::UYI, 'expected' => 0],
            'Currency VND with digits 0' => ['currency' => Currency::VND, 'expected' => 0],
            'Currency VUV with digits 0' => ['currency' => Currency::VUV, 'expected' => 0],
            'Currency XAF with digits 0' => ['currency' => Currency::XAF, 'expected' => 0],
            'Currency XOF with digits 0' => ['currency' => Currency::XOF, 'expected' => 0],
            'Currency XPF with digits 0' => ['currency' => Currency::XPF, 'expected' => 0],
            'Currency USD with digits 2' => ['currency' => Currency::USD, 'expected' => 2],
            'Currency EUR with digits 2' => ['currency' => Currency::EUR, 'expected' => 2],
            'Currency BHD with digits 3' => ['currency' => Currency::BHD, 'expected' => 3],
            'Currency IQD with digits 3' => ['currency' => Currency::IQD, 'expected' => 3],
            'Currency JOD with digits 3' => ['currency' => Currency::JOD, 'expected' => 3],
            'Currency KWD with digits 3' => ['currency' => Currency::KWD, 'expected' => 3],
            'Currency LYD with digits 3' => ['currency' => Currency::LYD, 'expected' => 3],
            'Currency OMR with digits 3' => ['currency' => Currency::OMR, 'expected' => 3],
            'Currency TND with digits 3' => ['currency' => Currency::TND, 'expected' => 3],
            'Currency CLF with digits 4' => ['currency' => Currency::CLF, 'expected' => 4],
            'Currency UYW with digits 4' => ['currency' => Currency::UYW, 'expected' => 4]
        ];
    }
}
