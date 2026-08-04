<?php

if (! function_exists('formatAmount')) {
    // format with 2 digits by default, but display more if necessary
    function formatAmount($value) {
        if ($value === null || $value === '') {
            return '';
        }

        // Normalize to float for comparison
        $float = (float) $value;

        // If rounding to 2 decimals changes the value → show full precision
        if (round($float, 2) == $float) {
            $str = number_format($float,2);
        } else {
            $str = number_format($float,6);
            $str = rtrim(rtrim($str, '0'), '.');
        }

        return $str;
    }
}

if (! function_exists('formatAmountNumeric')) {
    function formatAmountNumeric($value) {
        $str = formatAmount($value); // e.g. "1,730.00"

        // Remove all characters except digits and dot
        $str = preg_replace('/[^0-9.]/', '', $str);

        return $str; // "1730.00"
    }
}
    
if (! function_exists('formatQuantity')) {
    // format with 0 digits by default, but display more if necessary
    function formatQuantity($value) {
        if ($value === null || $value === '') {
            return '';
        }

        // Normalize to float for comparison
        $float = (float) $value;

        // If rounding to 0 decimals changes the value → show full precision
        if (round($float, 0) == $float) {
            $str = number_format($float,0);
        } else {
            $str = number_format($float,3);
            $str = rtrim(rtrim($str, '0'), '.');
        }

        return $str;
    }
}

if (! function_exists('formatQuantityNumeric')) {
    function formatQuantityNumeric($value) {
        $str = formatQuantity($value); // e.g. "1,234.5"

        // Remove all characters except digits and dot
        $str = preg_replace('/[^0-9.]/', '', $str);

        return $str; // "1234.5"
    }
}

if (! function_exists('isZeroAmount')) {
    function isZeroAmount($value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        // Round to 2 decimals using HALF_EVEN (Banker's rounding)
        $rounded = round((float)$value, 2, PHP_ROUND_HALF_EVEN);

        return $rounded == 0.0;
    }
}

use Carbon\Carbon;

function parseDateSmart(string $value): ?string
{
    $value = trim($value);

    // 1) dd.mm.yyyy or d.m.yyyy
    if (preg_match('/^\d{1,2}\.\d{1,2}\.\d{4}$/', $value)) {
        return Carbon::createFromFormat('d.m.Y', $value)->format('Y-m-d');
    }

    // 2) dd/mm/yyyy or d/m/yyyy
    if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $value)) {
        return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
    }

    // 3) yyyy-mm-dd (ISO)
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return $value; // already correct
    }

    // 4) Try Carbon's auto-parser (last resort)
    try {
        return Carbon::parse($value)->format('Y-m-d');
    } catch (\Exception $e) {
        return null;
    }
}
