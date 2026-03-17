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
        if (round($float, 2) != $float) {
            // Show raw value, but trim trailing zeros
            return rtrim(rtrim((string)$value, '0'), '.');
        }

        // Otherwise → force exactly 2 decimals
        return number_format($float, 2);
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
        if (round($float, 0) != $float) {
            // Show raw value, but trim trailing zeros
            return rtrim(rtrim((string)$value, '0'), '.');
        }

        // Otherwise → force exactly 0 decimals
        return number_format($float, 0);
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
