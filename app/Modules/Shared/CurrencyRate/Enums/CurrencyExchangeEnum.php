<?php

namespace App\Modules\Shared\CurrencyRate\Enums;

enum CurrencyExchangeEnum: int
{
    case DAILY = 0; // Это тип одного дня

    case WEEKLY = 1; // Это тип одного недели
}
