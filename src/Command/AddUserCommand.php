<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Utils\Validator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use function Symfony\Component\String\u;

/**
 * A console command that creates users and stores them in the database.
 *
 * To use this command, open a terminal window, enter into your project
 * directory and execute the following:
 *
 *     $ php bin/console app:add-user
 *
 * To output detailed information, increase the command verbosity:
 *
 *     $ php bin/console app:add-user -vv
 *
 * See https://symfony.com/doc/current/console.html
 *
 * We use the default services.yaml configuration, so command classes are registered as services.
 * See https://symfony.com/doc/current/console/commands_as_services.html
 *
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 */
#[AsCommand(
    name: 'app:add-user',
    description: 'Creates users and stores them in the database'
)]
final class AddUserCommand  extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly Validator $validator,
        private readonly UserRepository $users
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp($this->getCommandHelp())
            ->addArgument('username', InputArgument::OPTIONAL, 'The username of the new user')
            ->addArgument('password', InputArgument::OPTIONAL, 'The plain password of the new user')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'If set, the user is created as an administrator')
            ->addOption('gamemaster', null, InputOption::VALUE_NONE, 'If set, the user is created as a gamemaster');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');

        if (null !== $username && null !== $password) {
            return;
        }

        $this->io->title('Add User Command Interactive Wizard');
        $this->io->text([
            'If you prefer to not use this interactive wizard, provide the',
            'arguments required by this command as follows:',
            '',
            ' $ php bin/console app:add-user username password',
            '',
            'Now we\'ll ask you for the value of all the missing command arguments.',
        ]);

        if (null === $username) {
            $username = $this->io->ask('Username', null, $this->validator->validateUsername(...));
            $input->setArgument('username', $username);
        }

        if (null === $password) {
            $password = $this->io->askHidden('Password (your type will be hidden)', $this->validator->validatePassword(...));
            $input->setArgument('password', $password);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('add-user-command');

        $username = $input->getArgument('username');
        $plainPassword = $input->getArgument('password');

        $isAdmin = $input->getOption('admin');
        $isGamemaster = $input->getOption('gamemaster');

        $this->validateUserData($username, $plainPassword);

        $user = new User();
        $user->setLogin($username);

        if ($isAdmin) {
            $roles[] = User::ROLE_ADMIN;
        } elseif ($isGamemaster) {
            $roles[] = User::ROLE_GAMEMASTER;
        } else {
            $roles = [User::ROLE_USER];
        }

        $user->setRoles($roles);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $roleMessage = $isAdmin ? 'Administrator user' : ($isGamemaster ? 'Gamemaster user' : 'User');

        $this->io->success(sprintf(
            '%s was successfully created: %s (%s)',
            $roleMessage,
            $user->getLogin(),
            implode(', ', $roles)
        ));

        $event = $stopwatch->stop('add-user-command');

        if ($output->isVerbose()) {
            $this->io->comment(sprintf(
                'New user database id: %d / Elapsed time: %.2f ms / Consumed memory: %.2f MB',
                $user->getId(),
                $event->getDuration(),
                $event->getMemory() / (1024 ** 2)
            ));
        }

        return Command::SUCCESS;
    }

    private function validateUserData(string $username, string $plainPassword): void
    {
        $existingUser = $this->users->findOneBy(['login' => $username]);

        if (null !== $existingUser) {
            throw new RuntimeException(sprintf('There is already a user registered with the "%s" username.', $username));
        }

        $this->validator->validatePassword($plainPassword);
    }

    private function getCommandHelp(): string
    {
        return <<<'HELP'
        The <info>%command.name%</info> command creates new users and saves them in the database:

          <info>php %command.full_name%</info> <comment>username password</comment>

        By default the command creates regular users. To create administrator users,
        add the <comment>--admin</comment> option:

          <info>php %command.full_name%</info> username password <comment>--admin</comment>

        To create gamemaster users, add the <comment>--gamemaster</comment> option:

          <info>php %command.full_name%</info> username password <comment>--gamemaster</comment>

        If you omit any of the required arguments, the command will ask you to
        provide the missing values:

          # command will ask you for the password
          <info>php %command.full_name%</info> <comment>username</comment>

          # command will ask you for all arguments
          <info>php %command.full_name%</info>
        HELP;
    }
}
