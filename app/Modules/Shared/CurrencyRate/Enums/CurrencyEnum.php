<?php

namespace App\Modules\Shared\CurrencyRate\Enums;

enum CurrencyEnum: string
{
    case KGS = 'KGS';
    case RUB = 'RUB';
    case USD = 'USD';
    case EUR = 'EUR';
    case KZT = 'KZT';
    case CNY = 'CNY';
    case KGHS = 'KGHS'; // Валюта не существует
}
