<?php

namespace Aropixel\AdminBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'aropixel:make:crud',
    description: 'Creates a CRUD based on an existing FormType for AropixelAdminBundle',
)]
class MakeCrudCommand extends Command
{
    public function __construct(
        private readonly KernelInterface $kernel
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('entity-class', null, InputOption::VALUE_REQUIRED, 'Entity class (e.g. App\Entity\Popin)')
            ->addOption('form-class', null, InputOption::VALUE_REQUIRED, 'FormType class (e.g. App\Form\PopinType)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $entityClass = $input->getOption('entity-class');
        if (!$entityClass && $input->isInteractive()) {
            $entityClass = $io->ask('Entity class (e.g. App\Entity\Popin)');
        }
        if (!$entityClass) {
            $io->error('The entity class is required (use --entity-class or run in interactive mode).');
            return Command::FAILURE;
        }

        $formClass = $input->getOption('form-class');
        if (!$formClass && $input->isInteractive()) {
            $formClass = $io->ask('FormType class (e.g. App\Form\PopinType)');
        }
        if (!$formClass) {
            $io->error('The FormType class is required (use --form-class or run in interactive mode).');
            return Command::FAILURE;
        }

        $entityParts = explode('\\', $entityClass);
        $entityName = end($entityParts);
        $entityVar = lcfirst($entityName);

        $formParts = explode('\\', $formClass);
        $formName = end($formParts);

        $controllerName = $entityName . 'Controller';
        $namespace = 'App\\Controller\\Admin';
        
        $routePath = '/' . strtolower($entityName);
        $routeName = 'admin_' . strtolower($entityName);
        $templatePath = 'admin/' . strtolower($entityName);

        $repositoryClass = str_replace('\\Entity\\', '\\Repository\\', $entityClass) . 'Repository';
        $repositoryParts = explode('\\', $repositoryClass);
        $repositoryName = end($repositoryParts);
        $repositoryVar = lcfirst($repositoryName);

        $params = [
            'namespace' => $namespace,
            'entity_full_class_name' => $entityClass,
            'entity_class_name' => $entityName,
            'entity_var' => $entityVar,
            'form_full_class_name' => $formClass,
            'form_class_name' => $formName,
            'repository_full_class_name' => $repositoryClass,
            'repository_name' => $repositoryName,
            'repository_var' => $repositoryVar,
            'controller_name' => $controllerName,
            'route_path' => $routePath,
            'route_name' => $routeName,
            'template_path' => $templatePath,
        ];

        $projectDir = $this->kernel->getProjectDir();
        
        // Generate Controller
        $controllerFile = $projectDir . '/src/Controller/Admin/' . $controllerName . '.php';
        $this->generateFile('Controller.php.template', $controllerFile, $params, $io);

        // Generate Templates
        $templateDir = $projectDir . '/templates/' . $templatePath;
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0777, true);
        }

        $this->generateFile('index.html.twig.template', $templateDir . '/index.html.twig', $params, $io);
        $this->generateFile('form.html.twig.template', $templateDir . '/form.html.twig', $params, $io);

        $io->success('CRUD generated successfully!');
        $io->note('Don\'t forget to configure the DataTable columns in ' . $controllerName . '::index()');

        return Command::SUCCESS;
    }

    private function generateFile(string $templateName, string $targetPath, array $params, SymfonyStyle $io): void
    {
        $content = $this->getGeneratedContent($templateName, $params);

        $dir = dirname($targetPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($targetPath, $content);
        $io->writeln(sprintf('  <fg=green>generated</> %s', str_replace($this->kernel->getProjectDir().'/', '', $targetPath)));
    }

    private function getGeneratedContent(string $templateName, array $params): string
    {
        $templatePath = __DIR__ . '/../Resources/skeleton/crud/' . $templateName;
        $content = file_get_contents($templatePath);

        // Simple template engine: handle {{ variable }}
        foreach ($params as $key => $value) {
            if (is_string($value)) {
                $content = str_replace('{{ ' . $key . ' }}', $value, $content);
            }
        }

        // Simple handle {% if variable %} ... {% endif %}
        $content = preg_replace_callback('/^\s*\{% if (.*?) %\}\n?(.*?)\n?^\s*\{% endif %\}\r?\n?/m', function($matches) use ($params) {
            $condition = trim($matches[1]);
            $innerContent = $matches[2];
            if (isset($params[$condition]) && $params[$condition]) {
                return $innerContent . "\n";
            }
            return '';
        }, $content);

        // Simple handle {% if variable %} (inline)
        $content = preg_replace_callback('/\{% if (.*?) %\}(.*?)\{% endif %\}/s', function($matches) use ($params) {
            $condition = trim($matches[1]);
            $innerContent = $matches[2];
            if (isset($params[$condition]) && $params[$condition]) {
                return $innerContent;
            }
            return '';
        }, $content);

        return $content;
    }
}
