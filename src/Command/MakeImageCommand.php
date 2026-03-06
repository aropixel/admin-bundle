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

        $autoUpdate = $io->confirm('Voulez-vous mettre à jour automatiquement l\'entité parente ' . $parentClassName . ' ?', true);

        if ($autoUpdate) {
            $this->updateParentEntity($parentFullClassName, $params, $io);
        } else {
            $io->section('Code à ajouter dans ' . $parentClassName);
            $parentCode = $this->getGeneratedContent('ParentEntityMethods.php.template', $params);
            $parentCode = preg_replace('/\{% block (.*?) %\}\n?|\{% endblock %\}\n?/', '', $parentCode);
            $io->writeln('<fg=yellow>' . $parentCode . '</>');
        }

        return Command::SUCCESS;
    }

    private function updateParentEntity(string $parentFullClassName, array $params, SymfonyStyle $io): void
    {
        $projectDir = $this->kernel->getProjectDir();
        $parentFilePath = $projectDir . '/src/' . str_replace(['App\\', '\\'], ['', '/'], $parentFullClassName) . '.php';

        if (!file_exists($parentFilePath)) {
            $io->error('Impossible de trouver le fichier de l\'entité parente : ' . $parentFilePath);
            return;
        }

        $content = file_get_contents($parentFilePath);

        // Add use statement for the image entity if not present
        if (!str_contains($content, 'use ' . $params['entity_namespace'] . '\\' . $params['entity_class_name'] . ';') && $params['entity_namespace'] . '\\' . $params['entity_class_name'] !== $parentFullClassName) {
            $content = preg_replace('/namespace .*?;/', "$0\n\nuse " . $params['entity_namespace'] . '\\' . $params['entity_class_name'] . ';', $content);
        }

        // Get property and methods from template
        $templateContent = $this->getGeneratedContent('ParentEntityMethods.php.template', $params);
        preg_match('/\{% block property %\}(.*?)\{% endblock %\}/s', $templateContent, $propertyMatches);
        preg_match('/\{% block methods %\}(.*?)\{% endblock %\}/s', $templateContent, $methodsMatches);

        $newProperty = $propertyMatches[1] ?? '';
        $newMethods = $methodsMatches[1] ?? '';

        // 1. Insert property after last property or before first method
        if (!str_contains($content, 'private ?' . $params['entity_class_name'] . ' $' . $params['property_name'])) {
            if (preg_match_all('/private .*?;/s', $content, $matches, PREG_OFFSET_CAPTURE)) {
                $lastMatch = end($matches[0]);
                $pos = $lastMatch[1] + strlen($lastMatch[0]);
                $content = substr_replace($content, "\n" . $newProperty, $pos, 0);
            } else {
                // If no property found, try to find the class opening
                if (preg_match('/class [^{]+{/s', $content, $match, PREG_OFFSET_CAPTURE)) {
                    $pos = $match[0][1] + strlen($match[0][0]);
                    $content = substr_replace($content, "\n" . $newProperty, $pos, 0);
                }
            }
        }

        // 2. Insert methods before the last closing brace
        if (!str_contains($content, 'public function get' . $params['property_name_camel'] . '()')) {
            $pos = strrpos($content, '}');
            if ($pos !== false) {
                $content = substr_replace($content, $newMethods . "\n", $pos, 0);
            }
        }

        file_put_contents($parentFilePath, $content);
        $io->success('L\'entité parente ' . $parentFullClassName . ' a été mise à jour.');
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
