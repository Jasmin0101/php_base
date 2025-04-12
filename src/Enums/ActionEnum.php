<?php

namespace App\Enums;

enum ActionEnum: string
{
    case BUY = 'BUY';
    case SELL = 'SELL';

    public function getOpposite(): ActionEnum
    {
        return match ($this) {
            self::BUY => self::SELL,
            self::SELL => self::BUY,
        };
    }
}
