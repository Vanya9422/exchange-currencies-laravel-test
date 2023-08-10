<?php

namespace Database\Factories;

use App\Models\CurrencyExchange;
use Illuminate\Database\Eloquent\Factories\Factory;

class CurrencyExchangeFactory extends Factory
{
    protected $model = CurrencyExchange::class;

    public function definition()
    {
        return [
            'currency_code' => $this->faker->currencyCode,
            'nominal' => $this->faker->randomFloat(2, 0.01, 1000),
            'exchange_date' => $this->faker->date(),
            'rate' => round($this->faker->randomFloat(4, 0.1, 1000)),
            'type' => $this->faker->numberBetween(0, 1),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
