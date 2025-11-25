<?php
namespace App\Service;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use function PHPUnit\Framework\throwException;

class AppMailer
{
    private Mailer $mailer;

    public function __construct(string $dsn)
    {
        $transport = Transport::fromDsn($dsn);
        $this->mailer = new Mailer($transport);
    }

    public function send(Email $email): void
    {

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            throwException($e);
        }
    }
}
