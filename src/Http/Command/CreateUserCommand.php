<?php

namespace Aropixel\AdminBundle\Http\Command;

use Aropixel\AdminBundle\Domain\Activation\Email\ActivationEmailSenderInterface;
use Aropixel\AdminBundle\Domain\User\UserFactoryInterface;
use Aropixel\AdminBundle\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[AsCommand(name: 'aropixel:admin:create-user', description: 'Create a new admin user')]
class CreateUserCommand extends Command
{
    public function __construct(
        protected readonly ActivationEmailSenderInterface $activationEmailSender,
        protected readonly ManagerRegistry $managerRegistry,
        protected readonly UserFactoryInterface $userFactory,
        protected readonly ValidatorInterface $validator,
        protected string $adminLogin = 'admin',
        protected ?string $adminPassword = null,
        protected string $adminFirstName = '',
        protected string $adminLastName = 'Administrator',
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption('login', null, InputOption::VALUE_OPTIONAL, 'Login')
            ->addOption('password', null, InputOption::VALUE_OPTIONAL, 'Password')
            ->addOption('first_name', null, InputOption::VALUE_OPTIONAL, 'First Name')
            ->addOption('last_name', null, InputOption::VALUE_OPTIONAL, 'Last Name')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->adminLogin = $input->getOption('login') ?? 'admin';
        $this->adminPassword = $input->getOption('password') ?? null;
        $this->adminFirstName = $input->getOption('first_name') ?? '';
        $this->adminLastName = $input->getOption('last_name') ?? 'Administrator';
        if (null === $this->adminPassword) {
            $this->adminPassword = 'admin' === $this->adminLogin ? 'admin' : null;
        }
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $outputStyle = new SymfonyStyle($input, $output);
        $outputStyle->writeln('New admin user creation.');

        $this->adminLogin = $this->askAdminEmail($input, $output);
        $this->adminFirstName = $this->askAdminName('First Name : ', $input, $output);
        $this->adminLastName = $this->askAdminName('Last Name : ', $input, $output);
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $user = $this->userFactory->createUser();

            $user->setRoles(['ROLE_SUPER_ADMIN']);
            $user->setEmail($this->adminLogin);
            $user->setFirstName($this->adminFirstName);
            $user->setLastName($this->adminLastName);
            $user->setPlainPassword($this->adminPassword);

            $userClass = $this->userFactory->createUser()::class;

        } catch (\InvalidArgumentException) {
            return 0;
        }

        $em = $this->managerRegistry->getManagerForClass($this->userFactory->createUser()::class);
        $em->getRepository($userClass)->create($user);
        $em->flush();

        $outputStyle = new SymfonyStyle($input, $output);
        $outputStyle->writeln('<info>The admin user has been created successfully.</info>');
        $outputStyle->newLine();

        if ("admin" !== $this->adminLogin) {
            try {
                $this->activationEmailSender->sendActivationEmail($user);
            }
            catch (\Exception $e) {
                $outputStyle = new SymfonyStyle($input, $output);
                $outputStyle->writeln('<comment>The password creation email could not be sent.</comment>');
                $outputStyle->newLine();
            }
        }

        return Command::SUCCESS;
    }

    protected function askAdminEmail(InputInterface $input, OutputInterface $output): string
    {
        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');

        do {
            $question = $this->createEmailQuestion();
            $email = $questionHelper->ask($input, $output, $question);

            $repository = $this->managerRegistry->getRepository($this->userFactory->createUser()::class);
            $exists = null !== $repository->findOneBy(['email' => $email]);

            if ($exists) {
                $output->writeln('<info>This email already exists.</info>');
            }
        } while ($exists);

        return $email;
    }

    private function askAdminName($questionLabel, InputInterface $input, OutputInterface $output): ?string
    {
        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');
        $question = $this->createNameQuestion($questionLabel);

        return $questionHelper->ask($input, $output, $question);
    }

    private function askAdminPassword(InputInterface $input, OutputInterface $output): ?string
    {
        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');
        $passwordQuestion = $this->createPasswordQuestion('Password:');
        $confirmPasswordQuestion = $this->createPasswordQuestion('Password Confirmation:');

        do {
            $password = $questionHelper->ask($input, $output, $passwordQuestion);
            $repeatedPassword = $questionHelper->ask($input, $output, $confirmPasswordQuestion);

            if ($repeatedPassword !== $password && $input->isInteractive()) {
                $output->writeln('<error>Password are differents !</error>');
            }
        } while ($repeatedPassword !== $password);

        return $password;
    }

    private function createPasswordQuestion($questionLabel): Question
    {
        $validator = $this->getPasswordQuestionValidator();

        return (new Question($questionLabel))
            ->setValidator($validator)
            ->setMaxAttempts(3)
            ->setHidden(true)
            ->setHiddenFallback(false)
            ;
    }

    private function getPasswordQuestionValidator(): \Closure
    {
        return function ($value) {
            $errors = $this->validator->validate($value, [new NotBlank()]);
            foreach ($errors as $error) {
                throw new \DomainException($error->getMessage());
            }

            return $value;
        };
    }

    private function createEmailQuestion(): Question
    {
        return (new Question('Email: '))
            ->setValidator(function ($value) {
                /** @var ConstraintViolationListInterface $errors */
                $errors = $this->validator->validate((string) $value, [new Email(), new NotBlank()]);
                foreach ($errors as $error) {
                    throw new \DomainException($error->getMessage());
                }

                return $value;
            })
            ->setMaxAttempts(3)
            ;
    }

    private function createNameQuestion($text): Question
    {
        return (new Question($text . ': '))
            ->setValidator(function ($value) {
                /** @var ConstraintViolationListInterface $errors */
                $errors = $this->validator->validate((string) $value, [new NotBlank()]);
                foreach ($errors as $error) {
                    throw new \DomainException($error->getMessage());
                }

                return $value;
            })
            ->setMaxAttempts(3)
            ;
    }

}
