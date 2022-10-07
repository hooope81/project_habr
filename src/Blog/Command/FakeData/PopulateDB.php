<?php

namespace GeekBrains\LevelTwo\Blog\Command\FakeData;

use GeekBrains\LevelTwo\Blog\Comment;
use GeekBrains\LevelTwo\Blog\Name;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PopulateDB extends Command
{
    public function __construct(
        private \Faker\Generator $faker,
        private UsersRepositoryInterface $usersRepository,
        private PostsRepositoryInterface $postsRepository,
        private CommentsRepositoryInterface $commentsRepository
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('fake-data:populate-db')
            ->setDescription('Populates DB with fake data')
            ->addOption(
                'users-number',
                'u',
                InputOption::VALUE_OPTIONAL,
                'Redefine the number of users being created'
            )
             ->addOption(
                'posts-number',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Redefine the number of posts being created each user'
            )
            ->addOption(
                'comments-number',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Redefine the number of comments being created each user each post'
    );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $users = [];
        $posts = [];
        $numberUsers = $input->getOption('users-number') ?? 0;
        $numberPosts = $input->getOption('posts-number') ?? 0;
        $numberComments = $input->getOption('comments-number') ?? 0;
        for ($i = 0; $i < $numberUsers; $i++) {
            $user = $this->createFakeUser();
            $users[] = $user;
            $output->writeln('User created: ' . $user->getLogin());
        }
        foreach ($users as $user) {
            for ($i = 0; $i < $numberPosts; $i++) {
                $post = $this->createFakePost($user);
                $posts[] = $post;
                $output->writeln('Post created: '. $post->getTitle());
            }
            foreach ($posts as $post) {
                for ($i = 0; $i < $numberComments; $i++) {
                    $comment = $this->createFakeComment($user, $post);
                    $output->writeln('Comment created: ' . $comment->getUuid());

                }

            }
        }
        return Command::SUCCESS;
    }

    private function createFakeUser(): User{
        $user = User::createFrom(
            $this->faker->userName,
            $this->faker->password,
            new Name(
                $this->faker->firstName,
                $this->faker->lastName
            )
        );
        $this->usersRepository->save($user);
        return $user;
    }

    private function createFakePost(User $author): Post
    {
        $post = new Post(
            UUID::random(),
            $author,
            $this->faker->sentence(6, true),
            $this->faker->realText
        );
        $this->postsRepository->save($post);
        return $post;
    }

    private function createFakeComment(User $user, Post $post): Comment
    {
        $comment = new Comment(
            UUID::random(),
            $user,
            $post,
            $this->faker->realText
        );
        $this->commentsRepository->save($comment);
        return $comment;
    }
}