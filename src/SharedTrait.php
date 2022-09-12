<?php

namespace nutterz2009;

trait SharedTrait
{
    protected function stripHexPrefix(string $val): string
    {
        if (substr($val, 0, 2) === '0x') {
            return substr($val, 2, strlen($val) - 2) ?? '';
        }

        return $val;
    }

    protected function padHex(string $value): string {
        return strlen ($value) % 2 === 0 ? $value : "0{$value}";
    }
}
