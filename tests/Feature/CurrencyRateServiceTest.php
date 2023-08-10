<?php

namespace Tests\Feature;

use App\Models\CurrencyExchange;
use App\Modules\Shared\CurrencyRate\Enums\CurrencyEnum;
use App\Modules\Shared\CurrencyRate\Enums\CurrencyExchangeEnum;
use App\Modules\Shared\CurrencyRate\Services\CurrencyRateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CurrencyRateServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function testSyncToDbWithValidXml()
    {
        Http::fake([
            config('currency_rates.urls')[CurrencyExchangeEnum::DAILY->value] => Http::response('<root>Valid XML content</root>', 200),
            config('currency_rates.urls')[CurrencyExchangeEnum::WEEKLY->value] => Http::response('<root>Valid XML content</root>', 200),
        ]);

        $service = new CurrencyRateService();
        $service->syncToDb();

        // Проверяем, что в базе есть записи с валютами, которые мы добавили в фейковом XML
        $this->assertDatabaseHas('currency_exchanges', [
            'currency_code' => 'USD',
        ]);

        $this->assertDatabaseHas('currency_exchanges', [
            'currency_code' => 'EUR',
        ]);

        // Проверяем, что в базе нет записей с валютами, которых не было в фейковом XML
        $this->assertDatabaseMissing('currency_exchanges', [
            'currency_code' => 'KGHS',
        ]);
    }

    public function testGetCurrentRateForExistingCurrency()
    {
        // создаем запись в базе данных с определенным кодом валюты (например, USD) и соответствующим курсом
        CurrencyExchange::factory()->create([
            'currency_code' => CurrencyEnum::USD,
            'rate' => 87.87,
            'nominal' => 1,
        ]);

        $service = new CurrencyRateService();
        $rate = $service->getCurrentRate(CurrencyEnum::USD);

        // проверяем, что фактическое значение, которое вернул метод getCurrentRate, соответствует
        // ожидаемому значению (87.87 в данном случае).
        $this->assertEquals(87.87, $rate);
    }

    public function testGetCurrentRateForNonExistingCurrencyThrowsException()
    {
        $this->expectException(\RuntimeException::class);
        $service = new CurrencyRateService();
        $service->getCurrentRate(CurrencyEnum::KGHS);
    }

    public function testParseXmlResponseWithValidXml()
    {
        Http::fake([
            config('currency_rates.urls')[CurrencyExchangeEnum::DAILY->value] => Http::response('<root>Valid XML content</root>', 200),
        ]);

        $type = CurrencyExchangeEnum::DAILY;
        $service = new CurrencyRateService();
        $result = $service->parseXmlResponse(config('currency_rates.urls')[CurrencyExchangeEnum::DAILY->value], $type);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function testParseXmlResponseWithInvalidXmlThrowsException()
    {
        $this->expectException(\RuntimeException::class);

        $xml = '<invalid-xml>'; // Здесь указываете некорректное XML
        $type = CurrencyExchangeEnum::DAILY;

        $service = new CurrencyRateService();
        $service->parseXmlResponse($xml, $type);
    }
}
