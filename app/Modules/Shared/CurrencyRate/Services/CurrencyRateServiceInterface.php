<?php

namespace App\Modules\Shared\CurrencyRate\Services;

use App\Modules\Shared\CurrencyRate\Enums\CurrencyEnum;

interface CurrencyRateServiceInterface
{
    public function syncToDb(): void;

    public function getCurrentRate(CurrencyEnum $currency): float;
}
