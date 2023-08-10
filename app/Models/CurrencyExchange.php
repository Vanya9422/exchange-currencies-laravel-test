<?php

namespace App\Models;

use App\Modules\Shared\CurrencyRate\Enums\CurrencyExchangeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrencyExchange extends Model
{
    use HasFactory;

    protected $fillable = [
        'currency_code',
        'exchange_date',
        'rate',
        'type',
        'nominal',
    ];

    /**
     * @param array $currencies
     * @return void
     */
    public static function updateOrCreateMultipleCurrencies(array $currencies): void {
        foreach ($currencies as $currency) {
            static::updateOrCreate([
                'exchange_date' => $currency['exchange_date'],
                'type' => $currency['type'],
                'currency_code' => $currency['currency_code'],
            ], $currency);
        }
    }
}
