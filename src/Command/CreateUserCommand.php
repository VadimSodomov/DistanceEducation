<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\AuthUser;
use App\Enum\RoleEnum;
use App\Repository\AuthUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Консольная команда для создания пользователя
 */
#[AsCommand(
    name: 'create-auth-user',
    description: 'Создать объект AuthUser',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly AuthUserRepository          $authUserRepository,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly EntityManagerInterface      $entityManager,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'The email of the user.')
            ->addArgument('password', InputArgument::REQUIRED, 'The password of the user.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');

        $protocolUser = $this->authUserRepository->findOneByEmail($email);

        if (!is_null($protocolUser)) {
            $output->writeln('Пользователь уже существует');
            return Command::FAILURE;
        }
        if (strlen($password) < 5) {
            $output->writeln('Пароль должен содержать хотя бы 5 символов');
            return Command::FAILURE;
        }

        $user = new AuthUser();
        $user->update($email, $password, $this->userPasswordHasher);

        $user->addRole(RoleEnum::USER);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln("Пользователь \"{$user->getEmail()}\" создан");
        return Command::SUCCESS;
    }
}
