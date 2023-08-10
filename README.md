### Описание

Необходимо реализовать сервисный класс для парсинга курсов валют с сайта https://www.nbkr.kg, имплементирующий интерфейс
`App\Modules\Shared\CurrencyRate\Services\CurrencyRateServiceInterface`.

Результатом работы является работоспособность команды `App\Console\Commands\ParseCurrencyRatesCommand`, которая должны 
вывести в консоль перечень валют содержащихся в `CurrencyEnum` с текущим значением курса валюты, полученным путем
парсинга.

**!!! ВНЕСЕНИЕ ИЗМЕНЕНИЙ В ИНТЕРФЕЙС `App\Modules\Shared\CurrencyRate\Services\CurrencyRateServiceInterface` НЕ ДОПУСКАЕТСЯ !!!**

**!!! ВНЕСЕНИЕ ИЗМЕНЕНИЙ В КОД КОМАНДЫ `App\Console\Commands\ParseCurrencyRatesCommand` НЕ ДОПУСКАЕТСЯ !!!**

Пример вывода:

```shell
> php artisan app:parse-currency-rates

"KGS => 1" // app/Console/Commands/ParseCurrencyRatesCommand.php:31
"RUB => 0.9658" // app/Console/Commands/ParseCurrencyRatesCommand.php:31
"USD => 87.87" // app/Console/Commands/ParseCurrencyRatesCommand.php:31
"EUR => 98.4803" // app/Console/Commands/ParseCurrencyRatesCommand.php:31
"KZT => 0.1979" // app/Console/Commands/ParseCurrencyRatesCommand.php:31
"CNY => 12.3129" // app/Console/Commands/ParseCurrencyRatesCommand.php:31

   RuntimeException 

  KGHS => Rate not found

```

***

Сайтом https://www.nbkr.kg предоставляется 2 набора данных по курсам валют:

курсы валют на 1 день - https://www.nbkr.kg/XML/daily.xml

курсы валют на 1 неделю - https://www.nbkr.kg/XML/weekly.xml


- Одна валюта теоретически может содержаться в обеих выгрузках. Данные из ежедневной выгрузки являются более
приоритетными (в плане значений) чем из еженедельной.
- Локальной валютой для реализации данного задания является KGS (Киргизский сом). При реализации необходимо учесть,
что данная валюта отсутствует в выгрузках и ее курс всегда равен 1.
- Необходимо учесть что для каких-то валют в выгрузке возможно указание номинала отличного от 1, например 100, что 
означает соотношение обменного курса 1 Сом за 100 единиц выбранной валюты.
- Необходимо реализовать хранение исторических данных полученных курсов валют в БД с указанием даты, на которую курс
был получен.
- Необходимо предусмотреть вариант и выброс исключения, когда запрошенная валюта отсутствует в выгрузке.

Использование сторонних пакетов в разумных пределах не возбраняется.

**Обязательно:**
- Код должен работать на PHP 8.1 и выше.
- Покрытие кода сервиса тестами, замокать внешний сервис (данные, получаемые с внешнего сайта с курсами валют).
- Описание логики работы сервиса.
