<?php

namespace App\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/auth', name: 'api_auth_')]
class AuthController extends AbstractController
{
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepo,
        JWTEncoderInterface $jwtEncoder,
        MailerInterface $mailer,
        UrlGeneratorInterface $urlGenerator,
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        $name = $data['name'] ?? null;
        $surname = $data['surname'] ?? null;

        if (!$email || !$password) {
            return $this->json(['error' => 'Email and password are required'], 400);
        }

        if ($userRepo->findOneBy(['email' => $email])) {
            return $this->json(['error' => 'Email already registered'], 400);
        }

        $user = new User();
        $user->setEmail($email);
        $user->setName($name ?? '');
        $user->setSurname($surname ?? '');

        $hashedPassword = $passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $user->setVerifiedAt(null);
        $user->setRoles(['ROLE_USER']);

        $em->persist($user);
        $em->flush();

        $expiresAt = time() + 600;

        $verificationToken = $jwtEncoder->encode([
            'user_id' => $user->getId(),
            'email'   => $user->getEmail(),
            'type'    => 'email_verification',
            'exp'     => $expiresAt,
        ]);

        $verifyUrl = $urlGenerator->generate(
            'api_auth_verify_email',
            ['token' => $verificationToken],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $message = (new Email())
            ->from('no-reply@shopny.local')
            ->to($user->getEmail())
            ->subject('Verify your email')
            ->text("Click the link to verify your email:");

        $mailer->send($message);

        return $this->json([
            'message' => "User registered. Please check your email to verify your account. {$verifyUrl}",
        ], 201);
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        throw new \LogicException('This method is blank because the route is handled by the json_login firewall.');
    }

    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'surname' => $user->getSurname(),
            'roles' => $user->getRoles(),
            'verified' => $user->isVerified(),
        ]);
    }

    #[Route('/verify-email', name: 'verify_email', methods: ['GET'])]
    public function verifyEmail(
        Request $request,
        JWTEncoderInterface $jwtEncoder,
        EntityManagerInterface $em,
        UserRepository $userRepo
    ): JsonResponse {

        $token = $request->query->get('token');

        if (!$token) {
            return $this->json(['error' => 'Missing token'], 400);
        }

        try {
            $payload = $jwtEncoder->decode($token);
        } catch (JWTDecodeFailureException $e) {
            return $this->json(['error' => 'Invalid or expired token'], 400);
        }

        if (($payload['type'] ?? null) !== 'email_verification') {
            return $this->json(['error' => 'Invalid token type'], 400);
        }

        $userId = $payload['user_id'] ?? null;
        $email  = $payload['email'] ?? null;

        if (!$userId || !$email) {
            return $this->json(['error' => 'Invalid token payload'], 400);
        }

        /** @var User|null $user */
        $user = $userRepo->find($userId);

        if (!$user || $user->getEmail() !== $email) {
            return $this->json(['error' => 'User not found for this token'], 404);
        }

        if ($user->isVerified()) {
            return $this->json(['message' => 'Account already verified'], 200);
        }

        $user->setVerifiedAt(new \DateTimeImmutable());
        $em->flush();

        return $this->json(['message' => 'Email successfully verified. You can now log in.']);
    }

    #[Route('/resend-verification', name: 'resend_verification', methods: ['POST'])]
    public function resendVerification(
        Request $request,
        UserRepository $userRepo,
        JWTEncoderInterface $jwtEncoder,
        MailerInterface $mailer,
        UrlGeneratorInterface $urlGenerator,
        RateLimiterFactory $resendVerificationLimiter,
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        if (!$email) {
            return $this->json(['error' => 'Email is required'], 400);
        }

        /** @var User|null $user */
        $user = $userRepo->findOneBy(['email' => $email]);

        if (!$user) {
            return $this->json(['message' => 'If this email exists, a verification link has been sent.']);
        }

        if ($user->isVerified()) {
            return $this->json(['message' => 'Account is already verified.'], 200);
        }

        $key = $request->getClientIp() . '_' . $email;
        $limiter = $resendVerificationLimiter->create($key);
        $limit = $limiter->consume(1);

        if (!$limit->isAccepted()) {
            return $this->json([
                'error' => 'Too many requests. Please try again later.',
            ], 429);
        }

        $expiresAt = time() + 600;

        $verificationToken = $jwtEncoder->encode([
            'user_id' => $user->getId(),
            'email'   => $user->getEmail(),
            'type'    => 'email_verification',
            'exp'     => $expiresAt,
        ]);

        $verifyUrl = $urlGenerator->generate(
            'api_auth_verify_email',
            ['token' => $verificationToken],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $message = (new Email())
            ->from('no-reply@shopny.local')
            ->to($user->getEmail())
            ->subject('Verify your email (resend)')
            ->text("Click the link to verify your email: {$verifyUrl}");

        $mailer->send($message);

        return $this->json([
            'message' => 'Verification email has been resent (if the account exists and is not verified).',
        ]);
    }

    #[Route('/debug/send-test-email', name: 'api_debug_send_test_email', methods: ['GET'])]
    public function sendTestEmail(MailerInterface $mailer): JsonResponse
    {
        try {
            $email = (new Email())
                ->from('test@shopny.local')
                ->to('someaddress@example.com')
                ->subject('Test from Symfony')
                ->text('Hello from Symfony Mailer!');

            $mailer->send($email);

            return $this->json(['sent' => true]);
        } catch (TransportExceptionInterface $e) {
            return $this->json([
                'sent'  => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
