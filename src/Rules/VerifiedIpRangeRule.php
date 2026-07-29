<?php

namespace Bale\GupaPanel\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class VerifiedIpRangeRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail("The {$attribute} must be a string.");

            return;
        }

        if (! str_contains($value, '/')) {
            if (filter_var($value, FILTER_VALIDATE_IP) === false) {
                $fail("The {$attribute} '{$value}' is not a valid IP address.");
            }

            return;
        }

        $parts = explode('/', $value);

        if (count($parts) !== 2) {
            $fail("The {$attribute} '{$value}' is not a valid CIDR notation.");

            return;
        }

        if (filter_var($parts[0], FILTER_VALIDATE_IP) === false) {
            $fail("The {$attribute} '{$value}' has an invalid IP in CIDR.");
        }

        $prefix = (int) $parts[1];

        if ($prefix < 0 || $prefix > 32) {
            $fail("The {$attribute} '{$value}' has an invalid prefix length (must be 0-32).");
        }
    }
}
