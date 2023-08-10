<?php

namespace App\Console\Commands;

use App\Modules\Shared\CurrencyRate\Enums\CurrencyEnum;
use App\Modules\Shared\CurrencyRate\Services\CurrencyRateServiceInterface;
use Illuminate\Console\Command;

class ParseCurrencyRatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:parse-currency-rates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(CurrencyRateServiceInterface $service): int
    {
        foreach (CurrencyEnum::cases() as $currency) {
            dump($currency->value . " => " . $service->getCurrentRate($currency));
        }

        return Command::SUCCESS;
    }
}
