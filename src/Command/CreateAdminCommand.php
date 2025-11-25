<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Creates an admin user'
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface      $em,
        private readonly UserPasswordHasherInterface $hasher,
        private readonly UserRepository $users
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Admin email')
            ->addArgument('password', InputArgument::REQUIRED, 'Admin password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email');
        $password = $input->getArgument('password');

        if ($this->users->findOneBy(['email' => $email])) {
            $io->error("User with email $email already exists.");
            return Command::FAILURE;
        }

        $admin = new User();
        $admin->setEmail($email);
        $admin->setName('Admin');
        $admin->setSurname('User');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setVerifiedAt(new DateTimeImmutable());

        $hashed = $this->hasher->hashPassword($admin, $password);
        $admin->setPassword($hashed);

        $this->em->persist($admin);
        $this->em->flush();

        $io->success("Admin created: $email");

        return Command::SUCCESS;
    }
}
