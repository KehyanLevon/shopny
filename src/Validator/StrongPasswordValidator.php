<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class StrongPasswordValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof StrongPassword) {
            return;
        }

        if ($value === null) {
            return;
        }

        $valueTrimmed = trim($value);
        if ($valueTrimmed === '') {
            $this->context->buildViolation($constraint->noWhitespaceMessage)
                ->addViolation();
            return;
        }

        $errors = [];

        if (!preg_match('/[A-Z]/', $value)) {
            $errors[] = $constraint->uppercaseMessage;
        }

        if (!preg_match('/[a-z]/', $value)) {
            $errors[] = $constraint->lowercaseMessage;
        }

        if (!preg_match('/\d/', $value)) {
            $errors[] = $constraint->digitMessage;
        }

        if (!preg_match('/[\W_]/', $value)) {
            $errors[] = $constraint->specialMessage;
        }

        foreach ($errors as $error) {
            $this->context->buildViolation($error)->addViolation();
        }
    }
}
