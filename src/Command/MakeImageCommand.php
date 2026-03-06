<?php

namespace Aropixel\AdminBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'aropixel:make:image',
    description: 'Crée une entité Image (et éventuellement Crop) pour une entité donnée',
)]
class MakeImageCommand extends Command
{
    public function __construct(
        private readonly KernelInterface $kernel
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $parentFullClassName = $io->ask('Classe de l\'entité parente (ex: App\Entity\Artist)');
        if (!$parentFullClassName) {
            $io->error('La classe de l\'entité parente est requise.');
            return Command::FAILURE;
        }

        $propertyName = $io->ask('Nom de la propriété image (ex: image)', 'image');
        $isCroppable = $io->confirm('L\'image doit-elle être croppable ?', true);

        $parentParts = explode('\\', $parentFullClassName);
        $parentClassName = end($parentParts);
        $parentVar = lcfirst($parentClassName);
        $entityNamespace = implode('\\', array_slice($parentParts, 0, -1));

        $propertyNameCamel = ucfirst($propertyName);
        $imageClassName = $parentClassName . $propertyNameCamel;
        $cropClassName = $imageClassName . 'Crop';

        $params = [
            'entity_namespace' => $entityNamespace,
            'parent_full_class_name' => $parentFullClassName,
            'parent_class_name' => $parentClassName,
            'parent_var' => $parentVar,
            'property_name' => $propertyName,
            'property_name_camel' => $propertyNameCamel,
            'entity_class_name' => $imageClassName,
            'crop_class_name' => $cropClassName,
            'is_croppable' => $isCroppable,
        ];

        $projectDir = $this->kernel->getProjectDir();
        $entityDir = $projectDir . '/src/Entity';
        
        // Ensure entity directory exists (should exist in a Symfony app)
        if (!is_dir($entityDir)) {
            mkdir($entityDir, 0777, true);
        }

        // Generate Image Entity
        $imageFile = $entityDir . '/' . $imageClassName . '.php';
        $this->generateFile('ImageEntity.php.template', $imageFile, $params, $io);

        // Generate Crop Entity if needed
        if ($isCroppable) {
            $cropFile = $entityDir . '/' . $cropClassName . '.php';
            $this->generateFile('CropEntity.php.template', $cropFile, $params, $io);
        }

        $io->success('Entité(s) Image générée(s) avec succès !');

        $io->section('Code à ajouter dans ' . $parentClassName);
        $parentCode = $this->getGeneratedContent('ParentEntityMethods.php.template', $params);
        $io->writeln('<fg=yellow>' . $parentCode . '</>');

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
        $io->writeln(sprintf('  <fg=green>généré</> %s', str_replace($this->kernel->getProjectDir().'/', '', $targetPath)));
    }

    private function getGeneratedContent(string $templateName, array $params): string
    {
        $templatePath = __DIR__ . '/../Resources/skeleton/make-image/' . $templateName;
        $content = file_get_contents($templatePath);

        // Simple template engine: handle {{ variable }}
        foreach ($params as $key => $value) {
            if (is_string($value)) {
                $content = str_replace('{{ ' . $key . ' }}', $value, $content);
            }
        }

        // Simple handle {% if variable %} ... {% endif %}
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
