<?php

namespace TitaKita\Services\Infrastructure\CurrencyConversion;

use Brick\Money\Currency;
use TitaKita\Values\MoneyValue;

interface CurrencyConversionClientInterface
{
    public function convert(Currency $fromCurrency, Currency $toCurrency, float $amount): MoneyValue;
}
