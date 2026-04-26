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

        $entityName = $this->extractShortName($entityClass);
        $entityVar = lcfirst($entityName);

        $formName = $this->extractShortName($formClass);

        $controllerName = $entityName . 'Controller';
        $namespace = 'App\\Controller\\Admin';
        
        $entitySnakeCase = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $entityName));
        $routePath = '/' . str_replace('_', '-', $entitySnakeCase);
        $routeName = 'admin_' . $entitySnakeCase;
        $templatePath = 'admin/' . $entitySnakeCase;

        $repositoryClass = str_replace('\\Entity\\', '\\Repository\\', $entityClass) . 'Repository';
        $repositoryParts = explode('\\', $repositoryClass);
        $repositoryName = end($repositoryParts);
        $repositoryVar = lcfirst($repositoryName);

        $params = [
            'namespace' => $namespace,
            'entity_full_class_name' => $entityClass,
            'entity_class_name' => $entityName,
            'entity_var' => $entityVar,
            'entity_snake_case' => $entitySnakeCase,
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
        $this->generateFile('_actions.html.twig.template', $templateDir . '/_actions.html.twig', $params, $io);

        $io->success('CRUD generated successfully!');
        $io->note('Don\'t forget to configure the DataTable columns in ' . $controllerName . '::index()');

        return Command::SUCCESS;
    }

    /**
     * Extracts the short class name from a FQCN, even if namespace separators were stripped by the shell.
     * e.g. "App\Entity\Project" → "Project", "AppEntityProject" → "Project"
     */
    protected function extractShortName(string $fqcn): string
    {
        $fqcn = trim($fqcn, '\\');

        if (str_contains($fqcn, '\\')) {
            $parts = explode('\\', $fqcn);
            return end($parts);
        }

        if (str_contains($fqcn, '/')) {
            $parts = explode('/', $fqcn);
            return end($parts);
        }

        // Backslashes were stripped by shell — extract last PascalCase word
        if (preg_match('/([A-Z][a-z0-9]+)$/', $fqcn, $m)) {
            return $m[1];
        }

        return $fqcn;
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

        // Simple handle {{ 'string' ~ variable ~ 'string' }}
        $content = preg_replace_callback('/\{\{ (.*?) \}\}/', function($matches) use ($params) {
            $expression = trim($matches[1]);
            
            // Handle concatenation with ~
            if (str_contains($expression, '~')) {
                $parts = explode('~', $expression);
                $result = '';
                foreach ($parts as $part) {
                    $part = trim($part);
                    if ((str_starts_with($part, "'") && str_ends_with($part, "'")) || (str_starts_with($part, '"') && str_ends_with($part, '"'))) {
                        $result .= substr($part, 1, -1);
                    } elseif (isset($params[$part])) {
                        $result .= $params[$part];
                    } else {
                        // If we can't resolve it, return the original expression with delimiters
                        return '{{ ' . $expression . ' }}';
                    }
                }
                return $result;
            }
            
            // Handle simple variable replacement
            if (isset($params[$expression])) {
                return $params[$expression];
            }
            
            return $matches[0];
        }, $content);

        return $content;
    }
}
