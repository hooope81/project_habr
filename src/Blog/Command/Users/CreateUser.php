<?php

namespace GeekBrains\LevelTwo\Blog\Command\Users;

use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Name;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUser extends Command
{
    public function __construct(
        private UsersRepositoryInterface $usersRepository
    ){
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('users:create')
            ->setDescription('Creates new user')
            ->addArgument('first_name', InputArgument::REQUIRED, "First name")
            ->addArgument('last_name', InputArgument::REQUIRED, "Last name")
            ->addArgument('login', InputArgument::REQUIRED, 'Login')
            ->addArgument('password', InputArgument::REQUIRED, 'Password');
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ){
        $output->writeln('Create user command started');
        $login = $input->getArgument('login');
        if ($this->userExists($login)) {
            $output->writeln("User already exists: $login");
            return Command::FAILURE;
        }
        $user = User::createFrom(
            $login,
            $input->getArgument('password'),
            new Name(
                $input->getArgument('first_name'),
                $input->getArgument('last_name')
            )
        );
        $this->usersRepository->save($user);
        $output->writeln("User created: " . $user->getUuid());
        return Command::SUCCESS;
    }

    private function userExists(string $login): bool
    {
        try {
            $this->usersRepository->getByLogin($login);
        } catch (UserNotFoundException) {
            return false;
        }
        return true;
    }

}