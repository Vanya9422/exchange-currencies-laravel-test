<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ParseCurrencyRatesCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function testCommandOutput()
    {
        // Тестирование вывода команды со всеми валютами
        $this->artisan('app:parse-currency-rates')
            ->expectsOutput('KGS => ...')
            ->expectsOutput('RUB => ...')
            ->expectsOutput('USD => ...')
            ->expectsOutput('EUR => ...')
            ->expectsOutput('KZT => ...')
            ->expectsOutput('CNY => ...')
            ->expectsOutput('KGHS => Rate not found')
            ->assertExitCode(0);
    }
}
