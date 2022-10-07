<?php

namespace GeekBrains\LevelTwo\Blog\Command\Users;

use GeekBrains\LevelTwo\Blog\Name;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateUser extends Command
{
    public function __construct(
        private UsersRepositoryInterface $usersRepository
    ){
    parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('users:update')
            ->setDescription('Updates a user')
            ->addArgument(
            'uuid',
            InputArgument::REQUIRED,
            'UUID of a user to update'
        )
        ->addOption(
            'first-name',
            'f',
            InputOption::VALUE_OPTIONAL,
            'First name'
        )
        ->addOption(
            'last-name',
            'l',
            InputOption::VALUE_OPTIONAL,
            'Last name'
        );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $firstName = $input->getOption('first-name');
        $lastName = $input->getOption('last-name');

        if (empty($firstName) && empty($lastName)) {
            $output->writeln('Nothing to update');
            return Command::SUCCESS;
        }

        $uuid = new UUID($input->getArgument('uuid'));
        $user = $this->usersRepository->get($uuid);

        $updateName = new Name(
            firstName: empty($firstName)
                ? $user->getName()->getFirstName() : $firstName,
            lastName: empty($lastName)
                ? $user->getName()->getLastName() : $lastName
        );

        $updateUser = new User(
            uuid: $uuid,
            name: $updateName,
            login: $user->getLogin(),
            hashedPassword: $user->hashedPassword()
        );

        $this->usersRepository->save($updateUser);
        $output->writeln("User updated: $uuid");
        return Command::SUCCESS;
    }
}