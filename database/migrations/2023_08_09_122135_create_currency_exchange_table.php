<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('currency_exchanges', function (Blueprint $table) {
            $table->id();
            $table->string('currency_code')->index(); // Код валюты, например "USD"
            $table->date('exchange_date');
            $table->float('rate');
            $table->integer('nominal')->default(1);
            $table->tinyInteger('type')
                ->default(\App\Modules\Shared\CurrencyRate\Enums\CurrencyExchangeEnum::DAILY->value)
                ->index()
                ->comment('Информация о типах можно найти в файле App\Modules\Shared\CurrencyRate\Enums\CurrencyExchangeEnum');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_exchange');
    }
};
