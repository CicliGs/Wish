<?php

declare(strict_types=1);

namespace App\Support;

use Exception;
use Money\Money;
use Money\Currency;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Currencies\ISOCurrencies;

class MoneyService
{
    private static ISOCurrencies $currencies;
    private static DecimalMoneyFormatter $formatter;

    /**
     * Initialize static properties
     */
    private static function init(): void
    {
        if (!isset(self::$currencies)) {
            self::$currencies = new ISOCurrencies();
        }

        if (!isset(self::$formatter)) {
            self::$formatter = new DecimalMoneyFormatter(self::$currencies);
        }
    }

    /**
     * Create Money object from float value and currency code
     */
    public static function create(float $amount, string $currencyCode): Money
    {
        self::init();

        $currency = new Currency($currencyCode);
        $subunit = self::$currencies->subunitFor($currency);

        $minorUnits = (int) round($amount * (10 ** $subunit));

        return new Money($minorUnits, $currency);
    }

    /**
     * Create Money object from string value and currency code
     */
    public static function createFromString(string $amount, string $currencyCode): Money
    {
        return self::create((float) $amount, $currencyCode);
    }

    /**
     * Format Money object to display string
     */
    public static function format(Money $money): string
    {
        self::init();

        $amount = self::$formatter->format($money);
        $currencyCode = $money->getCurrency()->getCode();

        return self::formatCurrency($amount, $currencyCode);
    }

    /**
     * Format currency with proper symbol
     */
    private static function formatCurrency(string $amount, string $currencyCode): string
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'RUB' => '₽',
            'UAH' => '₴',
            'PLN' => 'zł',
            'JPY' => '¥',
            'CNY' => '¥',
            'KRW' => '₩',
            'INR' => '₹',
            'BRL' => 'R$',
            'CAD' => 'C$',
            'AUD' => 'A$',
            'CHF' => 'CHF',
            'SEK' => 'kr',
            'NOK' => 'kr',
            'DKK' => 'kr',
            'CZK' => 'Kč',
            'HUF' => 'Ft',
            'RON' => 'lei',
            'BGN' => 'лв',
            'HRK' => 'kn',
            'TRY' => '₺',
            'ILS' => '₪',
            'AED' => 'د.إ',
            'SAR' => 'ر.س',
            'QAR' => 'ر.ق',
            'KWD' => 'د.ك',
            'BHD' => 'د.ب',
            'OMR' => 'ر.ع',
            'JOD' => 'د.أ',
            'LBP' => 'ل.ل',
            'EGP' => 'ج.م',
            'MAD' => 'د.م',
            'TND' => 'د.ت',
            'DZD' => 'د.ج',
            'LYD' => 'د.ل',
            'SDG' => 'ج.س',
            'SOS' => 'S',
            'ETB' => 'Br',
            'KES' => 'KSh',
            'UGX' => 'USh',
            'TZS' => 'TSh',
            'RWF' => 'RF',
            'BIF' => 'FBu',
            'DJF' => 'Fdj',
            'ERN' => 'Nfk',
            'ZAR' => 'R',
            'BWP' => 'P',
            'SZL' => 'L',
            'LSL' => 'L',
            'NAD' => '$',
            'ZMW' => 'ZK',
            'ZWL' => '$',
            'AOA' => 'Kz',
            'CDF' => 'FC',
            'XAF' => 'FCFA',
            'XOF' => 'CFA',
            'MGA' => 'Ar',
            'MUR' => '₨',
            'SCR' => '₨',
            'MWK' => 'MK',
            'MZN' => 'MT'
        ];

        $symbol = $symbols[$currencyCode] ?? $currencyCode;

        $postfixCurrencies = ['PLN', 'CZK', 'HUF', 'SEK', 'NOK', 'DKK'];

        if (in_array($currencyCode, $postfixCurrencies)) {
            return $amount . ' ' . $symbol;
        }

        return $symbol . $amount;
    }

    /**
     * Check if currency code is valid
     */
    public static function isValidCurrency(string $currencyCode): bool
    {
        self::init();

        try {
            $currency = new Currency($currencyCode);
            return self::$currencies->contains($currency);
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Get list of supported currencies
     */
    public static function getSupportedCurrencies(): array
    {
        return [
            'USD' => 'US Dollar',
            'EUR' => 'Euro',
            'GBP' => 'British Pound',
            'RUB' => 'Russian Ruble',
            'UAH' => 'Ukrainian Hryvnia',
            'PLN' => 'Polish Zloty',
            'JPY' => 'Japanese Yen',
            'CNY' => 'Chinese Yuan',
            'CAD' => 'Canadian Dollar',
            'AUD' => 'Australian Dollar',
            'CHF' => 'Swiss Franc',
            'SEK' => 'Swedish Krona',
            'NOK' => 'Norwegian Krone',
            'DKK' => 'Danish Krone',
        ];
    }
}
