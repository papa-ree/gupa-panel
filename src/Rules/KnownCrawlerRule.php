<?php

namespace Bale\GupaPanel\Rules;

use Bale\GupaPanel\Models\KnownCrawler;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class KnownCrawlerRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail("The {$attribute} must be a string.");

            return;
        }

        $exists = KnownCrawler::where('user_agent_pattern', $value)
            ->orWhere('name', $value)
            ->exists();

        if ($exists) {
            $fail("The {$attribute} '{$value}' is already registered as a known crawler.");
        }
    }
}
