<?php

namespace App\Modules\Shared\CurrencyRate\Services;

use App\Models\CurrencyExchange;
use App\Modules\Shared\CurrencyRate\Enums\CurrencyEnum;
use App\Modules\Shared\CurrencyRate\Enums\CurrencyExchangeEnum;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CurrencyRateService implements CurrencyRateServiceInterface
{
    public function __construct()
    {
        // Вызывается метод для синхронизации данных при создании объекта сервиса
        $this->syncToDb();
    }

    /**
     * Синхронизирует данные о курсах валют с внешним источником и сохраняет их в базу данных.
     * @return void
     */
    public function syncToDb(): void
    {
        try {
            // Шаг 1: Получение данных о курсах валют с внешнего источника
            $currencies = $this->parseRates();

            // Шаг 2: Сохранение или обновление данных в базе данных внутри транзакции
            DB::transaction(function () use ($currencies) {
                CurrencyExchange::updateOrCreateMultipleCurrencies($currencies);
            });
        } catch (\Exception $e) {
            // Обработка ошибок, если что-то пошло не так
            Log::error('Error while synchronizing currency rates', [
                'exception' => $e,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }

    /**
     * Получает текущий курс валюты.
     *
     * @param CurrencyEnum $currency
     * @return float
     */
    public function getCurrentRate(CurrencyEnum $currency): float
    {
        // Если запрашиваемая валюта - KGS, то курс всегда равен 1
        if ($currency->value === CurrencyEnum::KGS->value) return 1.0; // Курс KGS всегда равен 1

        // Получение последнего доступного курса валюты из базы данных
        $latestRate = CurrencyExchange::where('currency_code', $currency->value)
            ->orderByDesc('created_at')
            ->first();

        // Если курс не найден, выбрасывается исключение
        if (!$latestRate) throw new \RuntimeException("$currency->value => Rate not found");

        // Возвращается расчитанный курс с учетом номинала
        return $latestRate->rate / $latestRate->nominal;
    }

    /**
     * Парсит данные о курсах валют из внешнего источника (XML).
     * @return array
     */
    private function parseRates(): array
    {
        // Получение URL для ежедневных и недельных курсов валют
        $dailyUrl = config('currency_rates.urls')[CurrencyExchangeEnum::DAILY->value];
        $weeklyUrl = config('currency_rates.urls')[CurrencyExchangeEnum::WEEKLY->value];

        // Шаг 1: Получение данных с внешнего источника (XML) для ежедневных и недельных курсов валют
        $dailyRates = $this->parseXmlResponse($dailyUrl, CurrencyExchangeEnum::DAILY);
        $weeklyRates = $this->parseXmlResponse($weeklyUrl, CurrencyExchangeEnum::WEEKLY);

        // Шаг 2: Объединение полученных курсов валют с учетом приоритетов
        return array_merge($dailyRates, $weeklyRates);
    }

    /**
     * Парсит XML-ответ, полученный из внешнего источника, и преобразует его в массив курсов валют.
     *
     * @param string $url
     * @param CurrencyExchangeEnum $type
     * @return array
     */
    public function parseXmlResponse(string $url, CurrencyExchangeEnum $type): array
    {
        $currencies = [];

        try {
            // Преобразовать строку xml в объект
            $xml = simplexml_load_string(file_get_contents($url));

            // Преобразование XML в ассоциативный массив
            $newArr = $this->convertXmlToArray($xml);

            // Проверка корректности структуры XML
            if (!isset($newArr['@attributes']['Date']) || !isset($newArr['Currency'])) {
                throw new \RuntimeException('Invalid XML format');
            }

            $date = $newArr['@attributes']['Date'];

            foreach ($newArr['Currency'] as $currency) {
                $isoCode = (string)$currency['@attributes']['ISOCode'];
                $nominal = (float)$currency['Nominal'];

                // Заменяем запятую на точку для корректного преобразования в число
                $value = str_replace(',', '.', (string)$currency['Value']);

                $currencies[] = [
                    'currency_code' => $isoCode,
                    'nominal' => $nominal,
                    'exchange_date' => Carbon::createFromFormat('d.m.Y', $date)->toDateString(),
                    'rate' => (float)$value,
                    'type' => $type->value
                ];
            }
        } catch (\Exception $e) {
            throw new \RuntimeException('Ошибка при обработке XML: ' . $e->getMessage());
        }

        return $currencies;
    }

    /**
     * Преобразует объект XML в массив.
     *
     * @param $xml
     * @return array
     */
    private function convertXmlToArray($xml): array
    {
        // Преобразовать XML в JSON
        $json = json_encode($xml);
        // Преобразовать JSON в ассоциативный массив
        return json_decode($json, true);
    }
}
