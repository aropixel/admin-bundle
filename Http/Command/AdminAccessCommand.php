<?php
/**
 * Created by PhpStorm.
 * User: Joël Gomez Caballe
 * Date: 02/10/2018
 * Time: 13:55
 */

namespace Aropixel\AdminBundle\Http\Command;

use Aropixel\AdminBundle\Domain\User\PasswordUpdaterInterface;
use Aropixel\AdminBundle\Domain\User\UserFactoryInterface;
use Aropixel\AdminBundle\Domain\User\UserRepositoryInterface;
use Aropixel\AdminBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;


/**
 * Commande de création du tout premier administrateur.
 *
 * @package Aropixel\AdminBundle\Command
 */
class AdminAccessCommand extends AbstractInstallCommand
{


    protected static $defaultName = 'aropixel:admin:setup';


    private PasswordUpdaterInterface $passwordUpdater;
    private UserFactoryInterface $userFactory;
    private UserRepositoryInterface $userRepository;



    public function __construct(EntityManagerInterface $em, ValidatorInterface $validator, PasswordUpdaterInterface $passwordUpdater, UserFactoryInterface $userFactory, UserRepositoryInterface $userRepository)
    {
        parent::__construct($em, $validator);
        $this->passwordUpdater = $passwordUpdater;
        $this->userFactory = $userFactory;
        $this->userRepository = $userRepository;
    }



    protected function configure()
    {

        $this
            ->setDescription('Initialisation du bundle d\'admin.')
            ->setHelp(<<<EOT
La commande <info>%command.name%</info> permet d'initialiser les données du bundle d'admin.
EOT
            )
        ;

    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $outputStyle = new SymfonyStyle($input, $output);
        $outputStyle->writeln('Création du compte administrateur.');


        try {
            $user = $this->userFactory->createUser();
            $admin = $this->configureNewUser($user, $input, $output);
        } catch (\InvalidArgumentException $exception) {
            return;
        }

        $admin->setRoles(['ROLE_SUPER_ADMIN']);
        $admin->setEnabled(true);
        $this->passwordUpdater->hashPlainPassword($admin);
        $this->em->flush();

        $outputStyle->writeln('<info>Le compte administrateur a bien été créé.</info>');
        $outputStyle->newLine();



        return defined( 'Command::SUCCESS' ) ? constant('Command::SUCCESS') : 0;

    }


    private function configureNewUser(
        User $user,
        InputInterface $input,
        OutputInterface $output
    ): User {

        $email = $this->getAdministratorEmail($input, $output);
        $user->setEmail($email);
        $user->setFirstName($this->getAdministratorName('Prénom', $input, $output));
        $user->setLastName($this->getAdministratorName('Nom', $input, $output));
        $user->setPlainPassword($this->getAdministratorPassword($input, $output));
        $this->em->persist($user);

        return $user;
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
        return (new Question($text.': '))
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

    private function getAdministratorEmail(InputInterface $input, OutputInterface $output): string
    {
        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');

        do {
            $question = $this->createEmailQuestion();
            $email = $questionHelper->ask($input, $output, $question);
            $exists = null !== $this->userRepository->findOneBy(array('email' => $email));

            if ($exists) {
                $output->writeln('<error>Cet email est déjà utilisé!</error>');
            }
        } while ($exists);

        return $email;
    }

    private function getAdministratorName($questionText, InputInterface $input, OutputInterface $output): string
    {
        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');

        $question = $this->createNameQuestion($questionText);
        $name = $questionHelper->ask($input, $output, $question);

        return $name;
    }

    private function getAdministratorPassword(InputInterface $input, OutputInterface $output): string
    {
        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');
        $validator = $this->getPasswordQuestionValidator();

        do {
            $passwordQuestion = $this->createPasswordQuestion('Entrez un mot de passe:', $validator);
            $confirmPasswordQuestion = $this->createPasswordQuestion('Entrez de nouveau le mot de passe:', $validator);

            $password = $questionHelper->ask($input, $output, $passwordQuestion);
            $repeatedPassword = $questionHelper->ask($input, $output, $confirmPasswordQuestion);

            if ($repeatedPassword !== $password) {
                $output->writeln('<error>Les mots de passes sont différents!</error>');
            }
        } while ($repeatedPassword !== $password);

        return $password;
    }

    private function getPasswordQuestionValidator(): \Closure
    {
        return function ($value) {
            /** @var ConstraintViolationListInterface $errors */
            $errors = $this->validator->validate($value, [new NotBlank()]);
            foreach ($errors as $error) {
                throw new \DomainException($error->getMessage());
            }

            return $value;
        };
    }

    private function createPasswordQuestion(string $message, \Closure $validator): Question
    {
        return (new Question($message))
            ->setValidator($validator)
            ->setMaxAttempts(3)
            ->setHidden(true)
            ->setHiddenFallback(false)
            ;
    }

}
