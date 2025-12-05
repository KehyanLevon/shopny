<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class StrongPassword extends Constraint
{
    public string $uppercaseMessage = 'Password must contain at least 1 uppercase letter.';
    public string $lowercaseMessage = 'Password must contain at least 1 lowercase letter.';
    public string $digitMessage     = 'Password must contain at least 1 digit.';
    public string $specialMessage   = 'Password must contain at least 1 special symbol like #@$%!.';
    public string $noWhitespaceMessage = 'Password cannot consist only of whitespace.';

    public function validatedBy(): string
    {
        return static::class.'Validator';
    }
}
