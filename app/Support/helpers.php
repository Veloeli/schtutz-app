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
