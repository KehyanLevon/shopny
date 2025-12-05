<?php

namespace App\Dto\Auth;

use Symfony\Component\Validator\Constraints as Assert;

class ResendVerificationRequest
{
    #[Assert\NotBlank(message: 'Email is required.', normalizer: 'trim')]
    #[Assert\Email(message: 'Invalid email format.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Email must not be longer than {{ limit }} characters.'
    )]
    public ?string $email = null;
}
