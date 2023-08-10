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

    /**
     * Проверяет синхронизацию данных о курсах валют с валидным XML.
     * Тест проверяет, что данные из фейковых XML-ответов корректно обрабатываются и сохраняются в базе данных.
     *
     * @return void
     */
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

    /**
     * Проверяет получение текущего курса существующей валюты.
     * Тест создает запись о курсе USD в базе данных и проверяет, что метод getCurrentRate корректно возвращает этот курс.
     *
     * @return void
     */
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

    /**
     * Проверяет выброс исключения при получении текущего курса несуществующей валюты.
     * Тест проверяет, что при попытке получить курс для несуществующей валюты выбрасывается исключение.
     *
     * @return void
     */
    public function testGetCurrentRateForNonExistingCurrencyThrowsException()
    {
        $this->expectException(\RuntimeException::class);
        $service = new CurrencyRateService();
        $service->getCurrentRate(CurrencyEnum::KGHS);
    }

    /**
     * Проверяет парсинг XML-ответа с валидным содержимым.
     * Тест проверяет, что метод parseXmlResponse корректно обрабатывает валидный XML-ответ и возвращает непустой массив.
     *
     * @return void
     */
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

    /**
     * Проверяет выброс исключения при парсинге невалидного XML-ответа.
     * Тест проверяет, что при попытке парсинга невалидного XML-ответа выбрасывается исключение.
     *
     * @return void
     */
    public function testParseXmlResponseWithInvalidXmlThrowsException()
    {
        $this->expectException(\RuntimeException::class);

        $xml = '<invalid-xml>'; // Здесь указываете некорректное XML
        $type = CurrencyExchangeEnum::DAILY;

        $service = new CurrencyRateService();
        $service->parseXmlResponse($xml, $type);
    }
}
